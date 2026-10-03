<?php

namespace App\Http\Controllers\Admin\Head;

use App\Exports\DynamicReportExport;
use App\Http\Controllers\Controller;
use App\Models\StockOpname;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameValidationController extends Controller
{
    public function index(Request $request)
    {
        $query = StockOpname::with(['book.category', 'book.rak', 'user'])
            ->where('status', 'menunggu_validasi')
            ->latest('submitted_at');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('book_code', 'like', "%{$search}%");
            });
        }

        $opnames = $query->paginate(10)->withQueryString();

        $stats = [
            'menunggu' => StockOpname::where('status', 'menunggu_validasi')->count(),
            'disetujui_bulan_ini' => StockOpname::where('status', 'disetujui')
                ->whereMonth('validated_at', now()->month)
                ->whereYear('validated_at', now()->year)
                ->count(),
            'ditolak_bulan_ini' => StockOpname::where('status', 'ditolak')
                ->whereMonth('updated_at', now()->month)
                ->whereYear('updated_at', now()->year)
                ->count(),
        ];

        return view('admin.head.stock-opname.index', compact('opnames', 'stats'));
    }

    public function show(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'menunggu_validasi') {
            return redirect()->route('head.stock-opname.index')
                ->with('error', 'Pengajuan ini sudah diproses.');
        }

        $stockOpname->load(['book.category', 'book.rak', 'user']);

        return view('admin.head.stock-opname.show', compact('stockOpname'));
    }

    public function approve(StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'menunggu_validasi') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $stockOpname->update([
            'status' => 'disetujui',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'rejection_reason' => null,
        ]);

        $stockOpname->applyToBook();

        return redirect()->route('head.stock-opname.index')
            ->with('success', 'Pengajuan stok opname disetujui dan stok buku telah diperbarui.');
    }

    public function reject(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'menunggu_validasi') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], [
            'rejection_reason.required' => 'Alasan penolakan wajib diisi.',
        ]);

        $stockOpname->update([
            'status' => 'ditolak',
            'rejection_reason' => $validated['rejection_reason'],
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('head.stock-opname.index')
            ->with('success', 'Pengajuan stok opname ditolak. Petugas dapat memperbaiki dan mengirim ulang.');
    }

    public function history(Request $request)
    {
        $query = StockOpname::with(['book', 'user', 'validator'])
            ->whereIn('status', ['disetujui', 'ditolak'])
            ->latest('validated_at');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('book', function ($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                    ->orWhere('book_code', 'like', "%{$search}%");
            });
        }

        $opnames = $query->paginate(10)->withQueryString();

        return view('admin.head.stock-opname.history', compact('opnames'));
    }

    public function export(Request $request)
    {
        $format = $request->format ?? 'pdf';
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        $data = StockOpname::with(['book.category', 'book.rak', 'user', 'validator'])
            ->where('status', 'disetujui')
            ->whereBetween('validated_at', [$startDate, $endDate])
            ->latest('validated_at')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data opname disetujui pada periode ini.');
        }

        $filename = 'Laporan-Stok-Opname-' . date('Y-m-d');
        $headers = [
            'Kode Buku',
            'Judul Buku',
            'Stok Baik',
            'Dipinjam',
            'Hilang Ditemukan',
            'Rusak Ringan',
            'Rusak Sedang',
            'Rusak Berat',
            'Selisih',
            'Petugas',
            'Validator',
            'Tanggal Validasi',
        ];

        if ($format === 'pdf') {
            $type = 'opname';
            $pdf = Pdf::loadView('stock-opname.pdf', compact('data', 'type', 'startDate', 'endDate', 'headers'));

            return $pdf->download($filename . '.pdf');
        }

        if ($format === 'excel') {
            $exportData = [];
            $no = 1;

            foreach ($data as $row) {
                $exportData[] = [
                    $no++,
                    $row->book->book_code ?? '-',
                    $row->book->title ?? '-',
                    $row->stok_fisik,
                    $row->stok_dipinjam,
                    $row->jumlah_hilang_ditemukan,
                    $row->jumlah_rusak_ringan,
                    $row->jumlah_rusak_sedang,
                    $row->jumlah_rusak_berat,
                    $row->selisihLabel(),
                    $row->user->name ?? '-',
                    $row->validator->name ?? '-',
                    $row->validated_at?->format('d M Y H:i') ?? '-',
                ];
            }

            array_unshift($headers, 'No.');

            return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
        }

        return back()->with('error', 'Format export tidak valid.');
    }
}
