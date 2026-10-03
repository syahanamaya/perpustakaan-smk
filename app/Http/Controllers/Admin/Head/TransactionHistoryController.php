<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use App\Models\Fine;
use Illuminate\Http\Request;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\DynamicReportExport;
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;

class TransactionHistoryController extends Controller
{
    public function index(Request $request)
    {
        // Mengambil data ringkasan (Summary)
        $total_transaksi = Borrowing::count();
        $transaksi_selesai = Borrowing::where('status', 'returned')->count();
        $transaksi_terlambat = Borrowing::where('status', 'borrowed')
            ->where('due_date', '<', now())->count();
        
        // Hitung total denda dengan mengambil datanya ke koleksi dan pakai abs() 
        // agar tidak bernilai 0 jika ada data minus di database (Sama seperti Dashboard)
        $total_fine = \Illuminate\Support\Facades\DB::table('fines')
            ->where('total_fine', '!=', 0)
            ->get()
            ->sum(function($transaksi) {
                return abs($transaksi->total_fine ?? 0);
            });

        // Query Utama dengan Filter (Tambahkan details.book)
        $query = Borrowing::query()->with(['student', 'details.book', 'user', 'fine'])->withCount('details');

        // Filter Pencarian
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->whereHas('student', function($sq) use ($request) {
                    $sq->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('nis', 'like', '%' . $request->search . '%');
                })
                ->orWhere('transaction_code', 'like', '%' . $request->search . '%')
                ->orWhereHas('details.book', function($bq) use ($request) {
                    $bq->where('title', 'like', '%' . $request->search . '%');
                });
            });
        }

        // Filter Status
        if ($request->status) {
            if ($request->status === 'Selesai') {
                $query->where('status', 'returned');
            } elseif ($request->status === 'Terlambat') {
                $query->where('status', 'borrowed')->where('due_date', '<', now());
            } elseif ($request->status === 'Dipinjam') {
                $query->where('status', 'borrowed')->where('due_date', '>=', now());
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);
        $transactions->appends($request->all());

        return view('admin.head.transactions.index', compact(
            'total_transaksi', 'transaksi_selesai', 'transaksi_terlambat', 
            'total_fine', 'transactions'
        ));
    }

    public function export(Request $request)
    {
        $format = $request->query('format', 'pdf');
        $query = Borrowing::query()->with(['student', 'details.book', 'user', 'fine'])->withCount('details');

        // Terapkan kembali filter yang sama dari halaman index
        if ($request->search) {
            $query->where(function($q) use ($request) {
                $q->whereHas('student', function($sq) use ($request) {
                    $sq->where('name', 'like', '%' . $request->search . '%')
                      ->orWhere('nis', 'like', '%' . $request->search . '%');
                })
                ->orWhere('transaction_code', 'like', '%' . $request->search . '%')
                ->orWhereHas('details.book', function($bq) use ($request) {
                    $bq->where('title', 'like', '%' . $request->search . '%');
                });
            });
        }

        if ($request->status) {
            if ($request->status === 'Selesai') {
                $query->where('status', 'returned');
            } elseif ($request->status === 'Terlambat') {
                $query->where('status', 'borrowed')->where('due_date', '<', now());
            } elseif ($request->status === 'Dipinjam') {
                $query->where('status', 'borrowed')->where('due_date', '>=', now());
            }
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();
        $filename = "Riwayat-Transaksi-" . date('Y-m-d');
        $headers = ['Kode Transaksi', 'Nama Siswa', 'Tanggal Pinjam', 'Tanggal Kembali/Jatuh Tempo', 'Status', 'Denda'];

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.head.reports.pdf', [
                'data' => $transactions, // Menggunakan 'data' agar cocok dengan view PDF generik
                'type' => 'riwayat transaksi',
                'headers' => $headers
            ]);
            return $pdf->download($filename . '.pdf');
        } elseif ($format === 'excel') {
            $exportData = [];
            $no = 1;
            foreach ($transactions as $row) {
                $exportData[] = [
                    $no++,
                    $row->transaction_code,
                    $row->student->name ?? '-',
                    Carbon::parse($row->borrow_date)->format('d M Y'),
                    $row->return_date ? Carbon::parse($row->return_date)->format('d M Y') : Carbon::parse($row->due_date)->format('d M Y'),
                    ucfirst($row->status),
                    'Rp ' . number_format($row->fine->total_fine ?? 0, 0, ',', '.')
                ];
            }
            array_unshift($headers, 'No.');
            return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
        }
        return back()->with('error', 'Format tidak valid!');
    }
}