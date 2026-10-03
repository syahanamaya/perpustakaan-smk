<?php

namespace App\Http\Controllers\Admin\Head;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Http\Request;
use App\Exports\FinesExport; // Pastikan ini ada
use Maatwebsite\Excel\Facades\Excel;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class FineRecapController extends Controller
{
    public function index(Request $request)
    {
        try {
            // 1. Ambil Parameter & Inisialisasi Tanggal
            $month = $request->input('month', Carbon::now()->month);
            $year = $request->input('year', Carbon::now()->year);
            $search = $request->input('search', ''); 
            $statusFilter = $request->input('status', '');

            $startDate = Carbon::createFromDate($year, $month, 1)->startOfMonth();
            $endDate = Carbon::createFromDate($year, $month, 1)->endOfMonth();

            // 2. Query Builder Utama
            // Kita ambil Borrowing yang punya denda > 0
            $borrowingQuery = Borrowing::with(['student', 'fine', 'details.book'])
                ->whereHas('fine', function ($q) {
                    $q->where('total_fine', '>', 0);
                });

            // Filter Berdasarkan Tanggal Pengembalian (Bulan yang dipilih)
            $borrowingQuery->whereBetween('return_date', [$startDate, $endDate]);

            // Filter Pencarian (Nama / NIS)
            if (!empty($search)) {
                $borrowingQuery->whereHas('student', function($q) use ($search) {
                    $q->where('name', 'like', "%{$search}%")
                      ->orWhere('nis', 'like', "%{$search}%");
                });
            }

            // Filter Status Denda (Paid / Unpaid)
            if (!empty($statusFilter)) {
                $borrowingQuery->whereHas('fine', function($q) use ($statusFilter) {
                    $q->where('fine_status', $statusFilter);
                });
            }

            // 3. Perhitungan Summary (Menggunakan Clone agar tidak merusak query pagination)
            $summaryQuery = clone $borrowingQuery;
            $summaryData = $summaryQuery->get();

            $total_fine = 0;
            $denda_dibayar = 0;
            $denda_terlambat = 0;
            $denda_kerusakan = 0;
            $denda_kehilangan = 0;

            foreach ($summaryData as $item) {
                if ($item->fine) {
                    $total_fine += (int) $item->fine->total_fine;
                    $denda_dibayar += (int) ($item->fine->denda_dibayar ?? 0);
                }
                foreach ($item->details as $detail) {
                    $denda_terlambat += (int) ($detail->late_fine ?? 0);
                    $kondisi = strtolower($detail->condition ?? '');
                    if ($kondisi === 'rusak') {
                        $denda_kerusakan += (int) ($detail->condition_fine ?? 0);
                    } elseif ($kondisi === 'hilang') {
                        $denda_kehilangan += (int) ($detail->condition_fine ?? 0);
                    }
                }
            }

            $denda_belum = $total_fine - $denda_dibayar;
            $total_transaksi_denda = $summaryData->count();
            $rata_rata_denda = $total_transaksi_denda > 0 ? $total_fine / $total_transaksi_denda : 0;
            $stok_rusak = \App\Models\Book::query()->sum('jumlah_rusak');
            $stok_hilang = \App\Models\Book::query()->sum('jumlah_hilang');

            // 5. Data Grafik (Sederhana)
            $chart_labels = ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun'];
            $chart_totals = [0, 0, 0, 0, $total_fine, 0]; 
            $chart_paid = [0, 0, 0, 0, $denda_dibayar, 0];

            // 6. Eksekusi Query untuk Tabel
            // Kita gunakan per-page 10
            $fines = $borrowingQuery->latest('return_date')
                                    ->paginate(10)
                                    ->appends($request->all());

            return view('admin.head.fines.recap', compact(
                'startDate', 'endDate', 'total_fine', 'denda_dibayar', 'denda_belum',
                'total_transaksi_denda', 'rata_rata_denda', 'denda_terlambat', 
                'denda_kerusakan', 'denda_kehilangan', 'chart_labels', 'chart_totals', 
                'chart_paid', 'fines', 'month', 'year', 'stok_rusak', 'stok_hilang'
            ));

        } catch (\Exception $e) {
            Log::error('Error di FineRecapController: ' . $e->getMessage());
            return back()->with('error', 'Terjadi kesalahan sistem.');
        }
    }
    public function export(Request $request)
    {
        $month = $request->input('month', now()->month);
        $year = $request->input('year', now()->year);
        
        $startDate = \Carbon\Carbon::createFromDate($year, $month, 1)->startOfMonth();
        $endDate = \Carbon\Carbon::createFromDate($year, $month, 1)->endOfMonth();

        // Ambil data yang ingin di-export
        $data = \App\Models\Borrowing::with(['student', 'fine'])
                ->whereHas('fine', function ($q) {
                    $q->where('total_fine', '>', 0);
                })
                ->whereBetween('return_date', [$startDate, $endDate])
                ->get();

        if ($request->format == 'xlsx') {
            $fileName = 'rekap-denda-' . $month . '-' . $year . '.xlsx';
            return Excel::download(new FinesExport($data), $fileName);
        }

        return back()->with('error', 'Format tidak didukung.');
    }
}