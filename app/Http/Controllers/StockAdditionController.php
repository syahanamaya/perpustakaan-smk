<?php

namespace App\Http\Controllers;

use App\Exports\DynamicReportExport;
use App\Models\Book;
use App\Models\StockAddition;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StockAdditionController extends Controller
{
    public function index(Request $request)
    {
        $query = StockAddition::with(['book.category', 'book.rak', 'user', 'validator'])
            ->latest('submitted_at')
            ->latest();

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

        $additions = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => StockAddition::count(),
            'menunggu' => StockAddition::where('status', 'menunggu_validasi')->count(),
            'ditolak' => StockAddition::where('status', 'ditolak')->count(),
            'disetujui' => StockAddition::where('status', 'disetujui')->count(),
        ];

        return view('stock-addition.index', compact('additions', 'stats'));
    }

    public function create(Request $request)
    {
        $books = Book::with(['category', 'rak'])
            ->withCount(['stockAdditions as pending_addition_count' => function ($q) {
                $q->where('status', 'menunggu_validasi');
            }])
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => [
                'id' => $book->id,
                'book_code' => $book->book_code,
                'title' => $book->title,
                'stock' => (int) $book->stock,
                'pending' => $book->pending_addition_count > 0,
            ]);

        $selectedBookId = old('book_id', $request->book_id);

        return view('stock-addition.create', compact('books', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'book_id' => 'required|exists:books,id',
            'jumlah_ditambah' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], [
            'book_id.required' => 'Pilih buku terlebih dahulu.',
            'jumlah_ditambah.required' => 'Jumlah stok ditambah wajib diisi.',
            'jumlah_ditambah.min' => 'Jumlah stok ditambah minimal 1.',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        if (StockAddition::where('book_id', $book->id)->where('status', 'menunggu_validasi')->exists()) {
            return back()->withInput()->with('error', 'Buku ini masih memiliki pengajuan penambahan stok yang menunggu validasi.');
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('stock-addition', 'public');
        }

        $jumlah = (int) $validated['jumlah_ditambah'];
        $sebelum = (int) $book->stock;

        StockAddition::create([
            'book_id' => $book->id,
            'user_id' => Auth::id(),
            'stok_sebelum' => $sebelum,
            'jumlah_ditambah' => $jumlah,
            'stok_sesudah' => $sebelum + $jumlah,
            'foto_path' => $fotoPath,
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'menunggu_validasi',
            'submitted_at' => now(),
        ]);

        return redirect()->route('admin.stock-addition.index')
            ->with('success', 'Pengajuan penambahan stok berhasil dikirim ke Kepala Perpustakaan.');
    }

    public function show(StockAddition $stockAddition)
    {
        $stockAddition->load(['book.category', 'book.rak', 'user', 'validator']);

        return view('stock-addition.show', compact('stockAddition'));
    }

    public function edit(StockAddition $stockAddition)
    {
        if (! $stockAddition->canEdit()) {
            return redirect()->route('admin.stock-addition.index')
                ->with('error', 'Pengajuan ini tidak dapat diperbaiki.');
        }

        $stockAddition->load(['book.category', 'book.rak']);

        return view('stock-addition.edit', compact('stockAddition'));
    }

    public function update(Request $request, StockAddition $stockAddition)
    {
        if (! $stockAddition->canEdit()) {
            return redirect()->route('admin.stock-addition.index')
                ->with('error', 'Pengajuan ini tidak dapat diperbaiki.');
        }

        $validated = $request->validate([
            'jumlah_ditambah' => 'required|integer|min:1',
            'catatan' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        $book = $stockAddition->book;
        $jumlah = (int) $validated['jumlah_ditambah'];
        $sebelum = (int) $book->stock;

        if ($request->hasFile('foto')) {
            if ($stockAddition->foto_path) {
                Storage::disk('public')->delete($stockAddition->foto_path);
            }
            $stockAddition->foto_path = $request->file('foto')->store('stock-addition', 'public');
        }

        $stockAddition->update([
            'stok_sebelum' => $sebelum,
            'jumlah_ditambah' => $jumlah,
            'stok_sesudah' => $sebelum + $jumlah,
            'catatan' => $validated['catatan'] ?? null,
            'status' => 'menunggu_validasi',
            'rejection_reason' => null,
            'validated_by' => null,
            'validated_at' => null,
            'resubmitted_at' => now(),
            'revision_count' => $stockAddition->revision_count + 1,
        ]);

        return redirect()->route('admin.stock-addition.index')
            ->with('success', 'Pengajuan penambahan stok berhasil dikirim ulang.');
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

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('stock-addition.pdf', compact('data', 'startDate', 'endDate', 'headers'));

            return $pdf->download($filename . '.pdf');
        }

        $exportData = [];
        $no = 1;
        foreach ($data as $row) {
            $exportData[] = [
                $no++,
                $row->book->book_code ?? '-',
                $row->book->title ?? '-',
                $row->stok_sebelum,
                $row->jumlah_ditambah,
                $row->stok_sesudah,
                $row->user->name ?? '-',
                $row->validator->name ?? '-',
                $row->validated_at?->format('d M Y H:i') ?? '-',
            ];
        }
        array_unshift($headers, 'No.');

        return Excel::download(new DynamicReportExport($exportData, $headers), "{$filename}.xlsx");
    }
}
