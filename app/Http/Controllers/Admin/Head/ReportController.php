<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\DynamicReportExport;

class ReportController extends Controller
{
    public function index(Request $request)
    {
        // 1. FILTER TANGGAL (Default: Bulan Ini)
        // Gunakan CamelCase ($startDate) agar sesuai dengan yang dipanggil di View Anda
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth()->endOfDay();
        $totalDays = $startDate->diffInDays($endDate) + 1;

        // 2. AMBIL DATA TRANSAKSI UTAMA (Penting: agar variabel $transactions terdefinisi)
        $transactions = Borrowing::with(['student', 'details.book'])
            ->whereBetween('borrow_date', [$startDate, $endDate])
            ->latest()
            ->get();

        // 3. KARTU RINGKASAN ATAS
        $total_borrowings = $transactions->count();
        
        $total_returns = Borrowing::where('status', 'returned')
            ->whereBetween('updated_at', [$startDate, $endDate])->count();
        
        // Menghitung siswa unik yang melakukan transaksi di periode ini
        $active_students = $transactions->pluck('student_id')->unique()->count();
        
        // PERBAIKAN: Hitung denda dari tabel Fine
        $total_fine = Fine::whereHas('borrowing', function($q) use ($startDate, $endDate) {
            $q->whereBetween('borrow_date', [$startDate, $endDate]);
        })->sum('total_fine');
        
        // Memisahkan denda terlambat dan denda lainnya
        $denda_terlambat = Fine::whereBetween('created_at', [$startDate, $endDate])
            ->where('keterangan', 'LIKE', '%terlambat%')
            ->sum('total_fine');
            
        $denda_lainnya = $total_fine - $denda_terlambat;

        // 4. GRAFIK PEMINJAMAN & PENGEMBALIAN (Per 5 Hari)
        $chart_labels = [];
        $chart_borrows = [];
        $chart_returns = [];

        $currentDate = clone $startDate;
        $intervalHari = 5;

        while ($currentDate <= $endDate) {
            $chunkEnd = clone $currentDate;
            $chunkEnd->addDays($intervalHari - 1)->endOfDay();

            if ($chunkEnd > $endDate) {
                $chunkEnd = clone $endDate;
            }

            if ($currentDate->format('Y-m-d') === $chunkEnd->format('Y-m-d')) {
                $label = $currentDate->translatedFormat('d M');
            } else {
                $label = $currentDate->format('d') . '-' . $chunkEnd->translatedFormat('d M');
            }
            $chart_labels[] = $label;

            $borrowsCount = Borrowing::whereBetween('borrow_date', [$currentDate, $chunkEnd])->count();
            $chart_borrows[] = $borrowsCount;

            $returnsCount = Borrowing::where('status', 'returned')
                ->whereBetween('updated_at', [$currentDate, $chunkEnd])
                ->count();
            $chart_returns[] = $returnsCount;

            $currentDate = $chunkEnd->addSecond()->startOfDay();
        }

        // 5. 5 BUKU PALING SERING DIPINJAM
        $top_books = DB::table('borrowing_details')
            ->join('books', 'borrowing_details.book_id', '=', 'books.id')
            ->join('borrowings', 'borrowing_details.borrowing_id', '=', 'borrowings.id')
            ->whereBetween('borrowings.borrow_date', [$startDate, $endDate])
            ->select('books.title', DB::raw('count(borrowing_details.book_id) as total'))
            ->groupBy('borrowing_details.book_id', 'books.title')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 6. RINGKASAN PERIODE
        $avg_borrows = $totalDays > 0 ? round($total_borrowings / $totalDays, 2) : 0;
        $avg_returns = $totalDays > 0 ? round($total_returns / $totalDays, 2) : 0;
        $new_students = Student::whereBetween('created_at', [$startDate, $endDate])->count();
        $new_books = Book::whereBetween('created_at', [$startDate, $endDate])->count();

        // 7. KATEGORI BUKU
        $categories = DB::table('borrowing_details')
            ->join('books', 'borrowing_details.book_id', '=', 'books.id')
            ->join('categories', 'books.category_id', '=', 'categories.id')
            ->join('borrowings', 'borrowing_details.borrowing_id', '=', 'borrowings.id')
            ->whereBetween('borrowings.borrow_date', [$startDate, $endDate])
            ->select('categories.name as category_name', DB::raw('count(borrowing_details.book_id) as total'))
            ->groupBy('categories.id', 'categories.name')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        if ($categories->isEmpty()) {
            $category_labels = ['Belum ada data'];
            $category_data = [0];
        } else {
            $category_labels = $categories->pluck('category_name')->toArray();
            $category_data = $categories->pluck('total')->toArray();
        }

        // 8. STATUS PEMINJAMAN
        $status_dipinjam = Borrowing::where('status', 'borrowed')
            ->where('due_date', '>=', Carbon::now())
            ->whereBetween('borrow_date', [$startDate, $endDate])->count();
        
        $status_terlambat = Borrowing::where('status', 'borrowed')
            ->where('due_date', '<', Carbon::now())->count();
            
        $status_selesai = $total_returns;

        // 9. SISWA TERAKTIF
        $top_students = Student::join('borrowings', 'students.id', '=', 'borrowings.student_id')
            ->select('students.*', DB::raw('COUNT(borrowings.id) as total_peminjaman'), DB::raw('MAX(borrowings.borrow_date) as terakhir_meminjam'))
            ->whereBetween('borrowings.borrow_date', [$startDate, $endDate])
            ->groupBy('students.id')
            ->orderBy('total_peminjaman', 'desc')
            ->take(5)
            ->get();

        // 10. BUKU PALING BANYAK DI-FAVORITKAN
        $top_favorites = DB::table('favorites')
            ->join('books', 'favorites.book_id', '=', 'books.id')
            ->whereBetween('favorites.created_at', [$startDate, $endDate])
            ->select('books.title', DB::raw('count(favorites.book_id) as total'))
            ->groupBy('favorites.book_id', 'books.title')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 11. KIRIM KE VIEW
        // Pastikan $transactions, $startDate, dan $endDate ikut dikirim
        return view('admin.head.reports.index', compact(
            'transactions', 'startDate', 'endDate', 'totalDays',
            'total_borrowings', 'total_returns', 'active_students', 'total_fine',
            'denda_terlambat', 'denda_lainnya',
            'chart_labels', 'chart_borrows', 'chart_returns',
            'top_books', 'avg_borrows', 'avg_returns', 'new_students', 'new_books',
            'category_labels', 'category_data',
            'status_dipinjam', 'status_terlambat', 'status_selesai',
            'top_students', 'top_favorites'
        ));
    }

    public function export(Request $request)
    {
        $type = $request->type;
        $format = $request->format;
        $startDate = $request->start_date ? Carbon::parse($request->start_date)->startOfDay() : Carbon::now()->startOfMonth()->startOfDay();
        $endDate = $request->end_date ? Carbon::parse($request->end_date)->endOfDay() : Carbon::now()->endOfMonth()->endOfDay();

        $data = [];
        $filename = '';
        $headers = [];

        if ($type === 'peminjaman') {
            $data = Borrowing::with(['student', 'details.book'])
                ->whereBetween('borrow_date', [$startDate, $endDate])
                ->latest()
                ->get();
            $filename = "Laporan-Peminjaman-" . date('Y-m-d');
            $headers = ['Kode Transaksi', 'Nama Siswa', 'Tanggal Pinjam', 'Jatuh Tempo', 'Status'];
        } elseif ($type === 'pengembalian') {
            $data = Borrowing::with(['student', 'details.book'])
                ->where('status', 'returned')
                ->whereBetween('return_date', [$startDate, $endDate])
                ->latest()
                ->get();
            $filename = "Laporan-Pengembalian-" . date('Y-m-d');
            $headers = ['Kode Transaksi', 'Nama Siswa', 'Tanggal Pinjam', 'Tanggal Kembali', 'Status'];
        } elseif ($type === 'denda') {
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            
            if ($request->filled('month') && $request->filled('year')) {
                $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
                $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();
            }

            $query = Fine::with(['borrowing.student'])
                ->whereHas('borrowing', function ($q) use ($startDate, $endDate) {
                    $q->where('status', 'returned')
                      ->whereBetween('return_date', [$startDate, $endDate]);
                })->where('total_fine', '!=', 0);

            if ($request->filled('search')) {
                $search = $request->search;
                $query->whereHas('borrowing.student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")->orWhere('nis', 'like', "%{$search}%");
                });
            }
            if ($request->filled('status')) {
                $query->where('fine_status', $request->status);
            }

            $data = $query->latest()->get();
            $filename = "Laporan-Denda-" . date('Y-m-d');
            $headers = ['Kode Transaksi', 'Nama Siswa', 'Total Denda', 'Denda Dibayar', 'Status Denda'];
        } elseif ($type === 'anggota') {
            $data = Student::whereBetween('created_at', [$startDate, $endDate])
                ->latest()
                ->get();
            $filename = "Laporan-Anggota-" . date('Y-m-d');
            $headers = ['NIS', 'Nama Siswa', 'Kelas', 'Jurusan', 'Status'];
        } elseif ($type === 'koleksi') {
            $data = Book::with(['category', 'rak'])
                ->latest()
                ->get();
            $filename = "Laporan-Koleksi-Buku-" . date('Y-m-d');
            $headers = ['Kode Buku', 'Judul Buku', 'Kategori', 'Rak', 'Stok'];
        }

        if ($data->isEmpty()) {
            return back()->with('error', 'Gagal mencetak, tidak ada data pada periode ini.');
        }

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.head.reports.pdf', compact('data', 'type', 'startDate', 'endDate', 'headers'));
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            $exportData = [];
            $no = 1;

            foreach ($data as $row) {
                if ($type === 'peminjaman') {
                    $exportData[] = [
                        $no++,
                        $row->transaction_code,
                        $row->student->name ?? '-',
                        $row->borrow_date,
                        $row->due_date,
                        $row->status
                    ];
                } elseif ($type === 'pengembalian') {
                    $exportData[] = [
                        $no++,
                        $row->transaction_code,
                        $row->student->name ?? '-',
                        $row->borrow_date,
                        $row->return_date,
                        $row->status
                    ];
                } elseif ($type === 'denda') {
                    $exportData[] = [
                        $no++,
                        $row->borrowing->transaction_code ?? '-',
                        $row->borrowing->student->name ?? '-',
                        $row->total_fine,
                        $row->denda_dibayar,
                        $row->fine_status
                    ];
                } elseif ($type === 'anggota') {
                    $exportData[] = [
                        $no++,
                        $row->nis,
                        $row->name,
                        $row->class,
                        $row->major,
                        $row->status
                    ];
                } elseif ($type === 'koleksi') {
                    $exportData[] = [
                        $no++,
                        $row->book_code,
                        $row->title,
                        $row->category->name ?? '-',
                        $row->rak->nama_rak ?? '-',
                        $row->stock
                    ];
                }
            }

            array_unshift($headers, 'No.'); // Tambahkan kolom No. di awal urutan headers
            
            return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
        }

        return back()->with('error', 'Tipe laporan atau format tidak valid!');
    }
}