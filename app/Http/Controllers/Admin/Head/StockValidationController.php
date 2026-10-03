<?php

namespace App\Http\Controllers\Admin\Head;

use App\Exports\DynamicReportExport;
use App\Http\Controllers\Controller;
use App\Models\StockAddition;
use App\Models\StockOpname;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Facades\Auth;
use Maatwebsite\Excel\Facades\Excel;

class StockValidationController extends Controller
{
    public function index(Request $request)
    {
        $jenis = $request->jenis; // opname | addition | null

        $opnames = collect();
        $additions = collect();

        if ($jenis !== 'addition') {
            $opnameQuery = StockOpname::with(['book', 'user'])->where('status', 'menunggu_validasi');
            if ($request->filled('search')) {
                $search = $request->search;
                $opnameQuery->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('book_code', 'like', "%{$search}%");
                });
            }
            $opnames = $opnameQuery->get()->map(fn (StockOpname $item) => $this->mapInboxItem('opname', $item));
        }

        if ($jenis !== 'opname') {
            $additionQuery = StockAddition::with(['book', 'user'])->where('status', 'menunggu_validasi');
            if ($request->filled('search')) {
                $search = $request->search;
                $additionQuery->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('book_code', 'like', "%{$search}%");
                });
            }
            $additions = $additionQuery->get()->map(fn (StockAddition $item) => $this->mapInboxItem('addition', $item));
        }

        $merged = $opnames->concat($additions)
            ->sortByDesc(fn ($item) => optional($item->submitted_at)->timestamp ?? 0)
            ->values();

        $items = $this->paginate($merged, 10);

        $stats = [
            'menunggu' => StockOpname::where('status', 'menunggu_validasi')->count()
                + StockAddition::where('status', 'menunggu_validasi')->count(),
            'menunggu_opname' => StockOpname::where('status', 'menunggu_validasi')->count(),
            'menunggu_addition' => StockAddition::where('status', 'menunggu_validasi')->count(),
        ];

        return view('admin.head.stock-validation.index', compact('items', 'stats'));
    }

    public function history(Request $request)
    {
        $jenis = $request->jenis;
        $status = $request->status;

        $opnames = collect();
        $additions = collect();

        if ($jenis !== 'addition') {
            $opnameQuery = StockOpname::with(['book', 'user', 'validator'])->whereIn('status', ['disetujui', 'ditolak']);
            if ($status) {
                $opnameQuery->where('status', $status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $opnameQuery->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('book_code', 'like', "%{$search}%");
                });
            }
            $opnames = $opnameQuery->get()->map(fn (StockOpname $item) => $this->mapInboxItem('opname', $item));
        }

        if ($jenis !== 'opname') {
            $additionQuery = StockAddition::with(['book', 'user', 'validator'])->whereIn('status', ['disetujui', 'ditolak']);
            if ($status) {
                $additionQuery->where('status', $status);
            }
            if ($request->filled('search')) {
                $search = $request->search;
                $additionQuery->whereHas('book', function ($q) use ($search) {
                    $q->where('title', 'like', "%{$search}%")->orWhere('book_code', 'like', "%{$search}%");
                });
            }
            $additions = $additionQuery->get()->map(fn (StockAddition $item) => $this->mapInboxItem('addition', $item));
        }

        $merged = $opnames->concat($additions)
            ->sortByDesc(fn ($item) => optional($item->validated_at)->timestamp ?? 0)
            ->values();

        $items = $this->paginate($merged, 10);

        return view('admin.head.stock-validation.history', compact('items'));
    }

    public function showOpname(StockOpname $stockOpname)
    {
        $stockOpname->load(['book.category', 'book.rak', 'user']);

        return view('admin.head.stock-validation.show-opname', compact('stockOpname'));
    }

    public function showAddition(StockAddition $stockAddition)
    {
        $stockAddition->load(['book.category', 'book.rak', 'user']);

        return view('admin.head.stock-validation.show-addition', compact('stockAddition'));
    }

    public function approveOpname(StockOpname $stockOpname)
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

        return redirect()->route('head.stock-validation.index')
            ->with('success', 'Stok opname disetujui. Data buku telah diperbarui.');
    }

    public function rejectOpname(Request $request, StockOpname $stockOpname)
    {
        if ($stockOpname->status !== 'menunggu_validasi') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']);

        $stockOpname->update([
            'status' => 'ditolak',
            'rejection_reason' => $validated['rejection_reason'],
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('head.stock-validation.index')
            ->with('success', 'Stok opname ditolak. Petugas dapat memperbaiki pengajuan.');
    }

    public function approveAddition(StockAddition $stockAddition)
    {
        if ($stockAddition->status !== 'menunggu_validasi') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $stokSesudah = (int) $stockAddition->book->stock + (int) $stockAddition->jumlah_ditambah;
        $stockAddition->update([
            'stok_sebelum' => (int) $stockAddition->book->stock,
            'stok_sesudah' => $stokSesudah,
            'status' => 'disetujui',
            'validated_by' => Auth::id(),
            'validated_at' => now(),
            'rejection_reason' => null,
        ]);
        $stockAddition->applyToBook();

        return redirect()->route('head.stock-validation.index')
            ->with('success', 'Penambahan stok disetujui. Stok buku telah bertambah.');
    }

    public function rejectAddition(Request $request, StockAddition $stockAddition)
    {
        if ($stockAddition->status !== 'menunggu_validasi') {
            return back()->with('error', 'Pengajuan ini sudah diproses.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'required|string|max:1000',
        ], ['rejection_reason.required' => 'Alasan penolakan wajib diisi.']);

        $stockAddition->update([
            'status' => 'ditolak',
            'rejection_reason' => $validated['rejection_reason'],
            'validated_by' => Auth::id(),
            'validated_at' => now(),
        ]);

        return redirect()->route('head.stock-validation.index')
            ->with('success', 'Penambahan stok ditolak. Petugas dapat memperbaiki pengajuan.');
    }

    public function export(Request $request)
    {
        $jenis = $request->jenis ?? 'opname';

        if ($jenis === 'addition') {
            return $this->exportAdditions($request);
        }

        return $this->exportOpnames($request);
    }

    private function exportOpnames(Request $request)
    {
        [$startDate, $endDate] = $this->period($request);
        $data = StockOpname::with(['book', 'user', 'validator'])
            ->where('status', 'disetujui')
            ->whereBetween('validated_at', [$startDate, $endDate])
            ->latest('validated_at')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data opname disetujui pada periode ini.');
        }

        $filename = 'Laporan-Stok-Opname-' . date('Y-m-d');
        $headers = ['Kode Buku', 'Judul Buku', 'Stok Baik', 'Dipinjam', 'Hilang', 'Rusak Ringan', 'Rusak Sedang', 'Rusak Berat', 'Selisih', 'Petugas', 'Validator', 'Tanggal Validasi'];

        if (($request->format ?? 'pdf') === 'pdf') {
            return Pdf::loadView('stock-opname.pdf', compact('data', 'startDate', 'endDate', 'headers'))->download($filename . '.pdf');
        }

        $exportData = [];
        $no = 1;
        foreach ($data as $row) {
            $exportData[] = [
                $no++, $row->book->book_code ?? '-', $row->book->title ?? '-', $row->stok_fisik, $row->stok_dipinjam,
                $row->jumlah_hilang_ditemukan, $row->jumlah_rusak_ringan, $row->jumlah_rusak_sedang, $row->jumlah_rusak_berat,
                $row->selisihLabel(), $row->user->name ?? '-', $row->validator->name ?? '-', $row->validated_at?->format('d M Y H:i'),
            ];
        }
        array_unshift($headers, 'No.');

        return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
    }

    private function exportAdditions(Request $request)
    {
        [$startDate, $endDate] = $this->period($request);
        $data = StockAddition::with(['book', 'user', 'validator'])
            ->where('status', 'disetujui')
            ->whereBetween('validated_at', [$startDate, $endDate])
            ->latest('validated_at')
            ->get();

        if ($data->isEmpty()) {
            return back()->with('error', 'Tidak ada data penambahan stok disetujui pada periode ini.');
        }

        $filename = 'Laporan-Penambahan-Stok-' . date('Y-m-d');
        $headers = ['Kode Buku', 'Judul Buku', 'Stok Sebelum', 'Jumlah Ditambah', 'Stok Sesudah', 'Petugas', 'Validator', 'Tanggal Validasi'];

        if (($request->format ?? 'pdf') === 'pdf') {
            return Pdf::loadView('stock-addition.pdf', compact('data', 'startDate', 'endDate', 'headers'))->download($filename . '.pdf');
        }

        $exportData = [];
        $no = 1;
        foreach ($data as $row) {
            $exportData[] = [
                $no++, $row->book->book_code ?? '-', $row->book->title ?? '-', $row->stok_sebelum, $row->jumlah_ditambah,
                $row->stok_sesudah, $row->user->name ?? '-', $row->validator->name ?? '-', $row->validated_at?->format('d M Y H:i'),
            ];
        }
        array_unshift($headers, 'No.');

        return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
    }

    private function mapInboxItem(string $jenis, StockOpname|StockAddition $item): object
    {
        $ringkasan = $jenis === 'opname'
            ? $item->selisihLabel() . ' · ' . $item->kondisiRingkasan()
            : '+' . $item->jumlah_ditambah . ' eksemplar (' . $item->stok_sebelum . ' → ' . $item->stok_sesudah . ')';

        return (object) [
            'jenis' => $jenis,
            'jenis_label' => $jenis === 'opname' ? 'Stok Opname' : 'Penambahan Stok',
            'id' => $item->id,
            'book' => $item->book,
            'user' => $item->user,
            'validator' => $item->validator ?? null,
            'status' => $item->status,
            'status_label' => $item->statusLabel(),
            'ringkasan' => $ringkasan,
            'submitted_at' => $item->submitted_at,
            'validated_at' => $item->validated_at,
            'show_url' => $jenis === 'opname'
                ? route('head.stock-validation.show-opname', $item)
                : route('head.stock-validation.show-addition', $item),
        ];
    }

    private function paginate($collection, int $perPage): LengthAwarePaginator
    {
        $page = LengthAwarePaginator::resolveCurrentPage();
        $items = $collection->forPage($page, $perPage)->values();

        return new LengthAwarePaginator($items, $collection->count(), $perPage, $page, [
            'path' => request()->url(),
            'query' => request()->query(),
        ]);
    }

    private function period(Request $request): array
    {
        $startDate = $request->start_date
            ? Carbon::parse($request->start_date)->startOfDay()
            : Carbon::now()->startOfMonth()->startOfDay();
        $endDate = $request->end_date
            ? Carbon::parse($request->end_date)->endOfDay()
            : Carbon::now()->endOfMonth()->endOfDay();

        return [$startDate, $endDate];
    }
}
