<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\Rak;
use App\Models\Category;
use App\Models\BorrowingDetail;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\DB;
use App\Exports\BookTemplateExport;
use Maatwebsite\Excel\Facades\Excel;


class BookController extends Controller
{
    /**
     * Display a listing of books with pagination.
     */
    public function index(Request $request)
    {
        // 1. HITUNG DATA DASAR
        $total_buku = Book::sum('stock') ?: 0;
        
        // Hitung TOTAL SELURUH buku yang sedang dipinjam (untuk info card di atas tabel)
        $buku_dipinjam = BorrowingDetail::whereHas('borrowing', function($q) {
            $q->whereIn('status', ['borrowed', 'late']) // Termasuk yang terlambat
              ->whereNull('return_date'); // Syarat utama: belum dikembalikan
        })->count();

        $buku_tersedia = $total_buku;
        $buku_rusak = (int) Book::sum('jumlah_rusak') + (int) Book::sum('jumlah_hilang');

        // 2. HITUNG PERSENTASE (Cek pembagi nol agar tidak error)
        $koleksi = $total_buku + $buku_dipinjam;
        $persen_tersedia = $koleksi > 0 ? round(($buku_tersedia / $koleksi) * 100) : 0;
        $persen_dipinjam = $koleksi > 0 ? round(($buku_dipinjam / $koleksi) * 100) : 0;
        $persen_rusak = $total_buku > 0 ? round(($buku_rusak / $total_buku) * 100) : 0;

        // 3. QUERY DAFTAR BUKU DENGAN PERHITUNGAN DINAMIS PER BUKU
        $query = Book::with(['category', 'rak'])
            ->withCount(['borrowingDetails as dipinjam_count' => function($q) {
                $q->whereHas('borrowing', function($query) {
                    $query->whereIn('status', ['borrowed', 'late']) // Termasuk yang terlambat
                          ->whereNull('return_date'); // Syarat utama: belum dikembalikan
                });
            }]);
        
        // Ringkasan penambahan stok yang sudah disetujui (dipakai untuk tampilan “keterangan opname” di halaman Data Buku)
        $query->withSum([
            'stockAdditions as addition_disetujui_total' => function ($q) {
                $q->where('status', 'disetujui');
            }
        ], 'jumlah_ditambah');

        // Filter Pencarian
        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('author', 'like', '%' . $request->search . '%')
                ->orWhere('isbn', 'like', '%' . $request->search . '%');
            });
        }

        // Filter Kategori & Rak
        if ($request->filled('category')) $query->where('category_id', $request->category);
        if ($request->filled('rak')) $query->where('rak_id', $request->rak);
        if ($request->filled('stock_status')) $query->where('stock_status', $request->stock_status);

        $books = $query->orderBy('created_at', 'desc')->paginate(10)->appends($request->all());
        
        $categories = Category::all();
        $raks = Rak::all();
        $opnameStats = app(StockOpnameController::class)->opnameStats();

        return view('books.index', compact(
        'total_buku', 'buku_tersedia', 'buku_dipinjam', 'buku_rusak',
        'persen_tersedia', 'persen_dipinjam', 'persen_rusak', 'books', 'categories', 'raks', 'opnameStats'
    ));
    }

    /**
     * Show the form for creating a new book.
     */
    public function create()
    {
        $categories = Category::all();
        $raks = Rak::all(); // Sudah benar
        return view('books.create', compact('categories', 'raks'));
    }

    /**
     * Store a newly created book in storage.
     */
    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_code' => 'required|string|unique:books,book_code',
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            'isbn' => 'nullable|string|max:20',
            'stock' => 'required|numeric|min:0',
            'category_id' => 'required|exists:categories,id', 
            'rak_id' => 'required|exists:raks,id', // TAMBAHKAN VALIDASI RAK
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('cover_image')) {
            $file = $request->file('cover_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('covers'), $filename);
            $validated['cover_image'] = $filename;
        }

        $validated['publication_year'] = $validated['publication_year'] ?? null;
        $validated['publisher'] = $validated['publisher'] ?? null;

        Book::create($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil ditambahkan dan disimpan di rak!');
    }

    /**
     * Show the form for editing the specified book.
     */
    public function edit(Book $book)
    {
        $categories = Category::all();
        $raks = Rak::all(); // TAMBAHKAN INI agar di halaman edit bisa ganti rak
        return view('books.edit', compact('book', 'categories', 'raks'));
    }

    /**
     * Update the specified book in storage.
     */
    public function update(Request $request, Book $book)
    {
        $validated = $request->validate([
            'book_code' => 'required|string|unique:books,book_code,' . $book->id,
            'title' => 'required|string|max:255',
            'author' => 'required|string|max:255',
            'publisher' => 'nullable|string|max:255',
            'publication_year' => 'nullable|integer|min:1900|max:' . date('Y'),
            // Tambahkan baris ini agar ISBN diizinkan masuk:
            'isbn' => 'nullable|string|max:20',
            'category_id' => 'required|exists:categories,id',
            'rak_id' => 'required|exists:raks,id',
            'cover_image' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
            'description' => 'nullable|string',
            'price' => 'nullable|numeric|min:0',
        ]);

        if ($request->hasFile('cover_image')) {
            if ($book->cover_image && File::exists(public_path('covers/' . $book->cover_image))) {
                File::delete(public_path('covers/' . $book->cover_image));
            }

            $file = $request->file('cover_image');
            $filename = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();
            $file->move(public_path('covers'), $filename);
            $validated['cover_image'] = $filename;
        }

        $validated['publication_year'] = $validated['publication_year'] ?? null;
        $validated['publisher'] = $validated['publisher'] ?? null;

        $book->update($validated);

        return redirect()->route('admin.books.index')
            ->with('success', 'Data buku dan lokasi rak berhasil diperbarui!');
    }

    public function destroy(Book $book)
    {
        if ($book->cover_image && File::exists(public_path('covers/' . $book->cover_image))) {
            File::delete(public_path('covers/' . $book->cover_image));
        }

        // $book->delete();

        return redirect()->route('admin.books.index')
            ->with('success', 'Buku berhasil dihapus!');
    }


    public function borrowHistory(Book $book)
    {
        $details = BorrowingDetail::with(['borrowing.student'])
            ->where('book_id', $book->id)
            ->whereHas('borrowing')
            ->orderByDesc('created_at')
            ->get();

        $borrowers = $details
            ->filter(fn ($d) => $d->borrowing && $d->borrowing->student)
            ->groupBy(fn ($d) => $d->borrowing->student_id)
            ->map(function ($items) {
                $student = $items->first()->borrowing->student;
                $last = $items->sortByDesc(function ($item) {
                    return optional($item->borrowing->borrow_date)->timestamp ?? 0;
                })->first();
                $status = $last->borrowing->status ?? '-';
                $statusLabel = match ($status) {
                    'borrowed', 'late' => 'Dipinjam',
                    'returned' => 'Dikembalikan',
                    'pending' => 'Menunggu',
                    default => ucfirst((string) $status),
                };

                return [
                    'name' => $student->name ?? '-',
                    'nis' => $student->nis ?? '-',
                    'class' => $student->class ?? '-',
                    'count' => $items->count(),
                    'last_date' => optional($last->borrowing->borrow_date)->translatedFormat('d M Y') ?? '-',
                    'last_status' => $statusLabel,
                ];
            })
            ->sortByDesc('count')
            ->values();

        $recent = $details->take(20)->map(function ($detail) {
            $student = $detail->borrowing->student ?? null;
            $status = $detail->borrowing->status ?? '-';

            return [
                'name' => $student->name ?? '-',
                'nis' => $student->nis ?? '-',
                'class' => $student->class ?? '-',
                'borrow_date' => optional($detail->borrowing->borrow_date)->translatedFormat('d M Y') ?? '-',
                'status' => match ($status) {
                    'borrowed', 'late' => 'Dipinjam',
                    'returned' => 'Dikembalikan',
                    'pending' => 'Menunggu',
                    default => ucfirst((string) $status),
                },
            ];
        })->values();

        return response()->json([
            'total' => $details->count(),
            'borrower_count' => $borrowers->count(),
            'borrowers' => $borrowers,
            'recent' => $recent,
        ]);
    }

    public function template()
    {
        return Excel::download(new BookTemplateExport, 'template_buku.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|max:5120'
        ], [
            'file.required' => 'Pilih file terlebih dahulu.',
            'file.mimes' => 'Format file harus .xlsx, .xls, atau .csv.',
            'file.max' => 'Ukuran file tidak boleh lebih dari 5MB.'
        ]);

        $berhasil = 0; $gagal = 0;

        DB::beginTransaction();
        try {
            $data = Excel::toArray(new class {}, $request->file('file'));
            
            if (empty($data) || empty($data[0])) {
                return back()->with('error', 'File kosong.');
            }

            $rows = $data[0];
            array_shift($rows); // Lewati Baris Judul

            foreach ($rows as $row) {
                if (count($row) < 9 || empty($row[0])) { $gagal++; continue; }
                if (Book::where('book_code', '=', $row[0], 'and')->exists()) { $gagal++; continue; } // Skip kode duplikat

                Book::create([
                    'book_code' => $row[0], 'title' => $row[1], 'author' => $row[2],
                    'publisher' => $row[3] ?: null,
                    'publication_year' => $row[4] ?: null, 'isbn' => $row[5] ?: null, 'stock' => (int)$row[6],
                    'category_id' => (int)$row[7], 'rak_id' => (int)$row[8],
                    'price' => isset($row[9]) && is_numeric($row[9]) ? (int)$row[9] : 0,
                    'description' => isset($row[10]) ? $row[10] : null,
                ]);
                $berhasil++;
            }
            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack(); return back()->with('error', 'Gagal import: ' . $e->getMessage());
        }
        return back()->with('success', "Import selesai! Berhasil: $berhasil data, Gagal/Dilewati: $gagal data.");
    }

    public function collection(Request $request)
    {
        // 1. DATA RINGKASAN (CARDS)
        $total_buku = Book::sum('stock');
        // Asumsi ada kolom 'status' atau relasi peminjaman untuk menghitung ini
        $buku_dipinjam = DB::table('borrowing_details')->count(); 
        $buku_tersedia = $total_buku - $buku_dipinjam;
        $buku_rusak = 0; // Sesuaikan jika ada kolom kondisi_buku

        // 2. DATA GRAFIK PIE (KATEGORI)
        $categories_stats = Category::withCount('books')->get();
        $pie_labels = $categories_stats->pluck('name');
        $pie_data = $categories_stats->pluck('books_count');

        // 3. BUKU PALING SERING DIPINJAM
        $top_books = DB::table('borrowing_details')
            ->join('books', 'borrowing_details.book_id', '=', 'books.id')
            ->select('books.title', 'books.author', 'books.cover_image', DB::raw('count(borrowing_details.book_id) as total'))
            ->groupBy('borrowing_details.book_id', 'books.title', 'books.author', 'books.cover_image')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 4. DATA GRAFIK BAR (RAK)
        $rak_stats = Rak::withCount('books')->get();
        $bar_labels = $rak_stats->pluck('nama_rak');
        $bar_data = $rak_stats->pluck('books_count');

        // 5. DAFTAR BUKU (TABLE) DENGAN FILTER
        $query = Book::with(['category', 'rak']);

        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('isbn', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
        }

        if ($request->filled('category')) {
            $query->where('category_id', $request->category);
        }

        $books = $query->paginate(10)->appends($request->all());
        $categories = Category::all();
        $raks = Rak::all();

        return view('books.collection', compact(
            'books', 'categories', 'raks', 'total_buku', 'buku_tersedia', 
            'buku_dipinjam', 'buku_rusak', 'pie_labels', 'pie_data', 
            'top_books', 'bar_labels', 'bar_data'
        ));
    }
}