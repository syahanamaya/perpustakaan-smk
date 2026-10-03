<?php

namespace App\Http\Controllers;

use App\Exports\DynamicReportExport;
use App\Models\Book;
use App\Models\BorrowingDetail;
use App\Models\FineSetting;
use App\Models\StockOpname;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Maatwebsite\Excel\Facades\Excel;

class StockOpnameController extends Controller
{
    private function damagePercents(): array
    {
        $setting = FineSetting::query()->latest()->first();

        return [
            'ringan' => (int) ($setting?->damage_light_percent ?? 25),
            'sedang' => (int) ($setting?->damage_medium_percent ?? 50),
            'berat' => (int) ($setting?->damage_heavy_percent ?? 75),
        ];
    }

    private function conditionValidationRules(): array
    {
        return [
            'jumlah_hilang_ditemukan' => 'nullable|integer|min:0',
            'jumlah_rusak_ringan' => 'nullable|integer|min:0',
            'jumlah_rusak_sedang' => 'nullable|integer|min:0',
            'jumlah_rusak_berat' => 'nullable|integer|min:0',
        ];
    }

    private function mapBookForForm(Book $book): array
    {
        $dipinjam = $book->borrowedCount();
        $master = $book->masterCopyCount($dipinjam);

        return [
            'id' => $book->id,
            'book_code' => $book->book_code,
            'title' => $book->title,
            'category' => $book->category->name ?? '-',
            'rak' => $book->rak->nama_rak ?? '-',
            'stok_sistem' => (int) $book->stock,
            'stok_dipinjam' => $dipinjam,
            'stok_master' => $master,
            'jumlah_hilang_sistem' => (int) ($book->jumlah_hilang ?? 0),
            'jumlah_rusak_ringan_sistem' => (int) ($book->jumlah_rusak_ringan ?? 0),
            'jumlah_rusak_sedang_sistem' => (int) ($book->jumlah_rusak_sedang ?? 0),
            'jumlah_rusak_berat_sistem' => (int) ($book->jumlah_rusak_berat ?? 0),
            'has_pending' => $book->stockOpnames()
                ->where('status', 'menunggu_validasi')
                ->exists(),
        ];
    }

    private function buildOpnamePayload(Book $book, array $validated): array
    {
        $dipinjam = $book->borrowedCount();
        $master = $book->masterCopyCount($dipinjam);
        $hilang = (int) ($validated['jumlah_hilang_ditemukan'] ?? 0);
        $rusakRingan = (int) ($validated['jumlah_rusak_ringan'] ?? 0);
        $rusakSedang = (int) ($validated['jumlah_rusak_sedang'] ?? 0);
        $rusakBerat = (int) ($validated['jumlah_rusak_berat'] ?? 0);

        return [
            'stok_sistem' => (int) $book->stock,
            'stok_dipinjam' => $dipinjam,
            'stok_master' => $master,
            'jumlah_hilang_sistem' => (int) ($book->jumlah_hilang ?? 0),
            'jumlah_rusak_ringan_sistem' => (int) ($book->jumlah_rusak_ringan ?? 0),
            'jumlah_rusak_sedang_sistem' => (int) ($book->jumlah_rusak_sedang ?? 0),
            'jumlah_rusak_berat_sistem' => (int) ($book->jumlah_rusak_berat ?? 0),
            'stok_fisik' => (int) $validated['stok_fisik'],
            'jumlah_hilang_ditemukan' => $hilang,
            'jumlah_rusak_ringan' => $rusakRingan,
            'jumlah_rusak_sedang' => $rusakSedang,
            'jumlah_rusak_berat' => $rusakBerat,
            'selisih' => StockOpname::calculateSelisih(
                (int) $validated['stok_fisik'],
                $dipinjam,
                $master,
                $hilang,
                $rusakRingan,
                $rusakSedang,
                $rusakBerat
            ),
            'catatan' => $validated['catatan'] ?? null,
        ];
    }

    public function opnameStats(): array
    {
        $totalJudul = Book::count();
        $masalah = Book::whereIn('stock_status', ['missing', 'damaged'])->count();
        $unverified = 0;
        $verified = max(0, $totalJudul - $masalah);

        return [
            'total_judul' => $totalJudul,
            'opname_verified' => $verified,
            'opname_unverified' => $unverified,
            'opname_masalah' => $masalah,
            'persen_verified' => $totalJudul > 0 ? round(($verified / $totalJudul) * 100, 1) : 0,
            'total_buku' => (int) Book::sum('stock'),
            'buku_dipinjam' => BorrowingDetail::whereHas('borrowing', function ($q) {
                $q->whereIn('status', ['borrowed', 'late'])->whereNull('return_date');
            })->count(),
            'buku_rusak' => (int) Book::sum('jumlah_rusak') + (int) Book::sum('jumlah_hilang'),
        ];
    }

    public function index(Request $request)
    {
        $query = StockOpname::with(['book.category', 'book.rak', 'user', 'validator'])
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

        if ($request->filled('start_date')) {
            $query->whereDate('submitted_at', '>=', $request->start_date);
        }

        if ($request->filled('end_date')) {
            $query->whereDate('submitted_at', '<=', $request->end_date);
        }

        $opnames = $query->paginate(10)->withQueryString();

        $stats = [
            'total' => StockOpname::count(),
            'menunggu' => StockOpname::where('status', 'menunggu_validasi')->count(),
            'ditolak' => StockOpname::where('status', 'ditolak')->count(),
            'disetujui' => StockOpname::where('status', 'disetujui')->count(),
        ];

        return view('stock-opname.index', compact('opnames', 'stats'));
    }

    public function create(Request $request)
    {
        $books = Book::with(['category', 'rak'])
            ->orderBy('title')
            ->get()
            ->map(fn (Book $book) => $this->mapBookForForm($book));

        $persenRusak = $this->damagePercents();
        $selectedBookId = old('book_id', $request->book_id);

        return view('stock-opname.create', compact('books', 'persenRusak', 'selectedBookId'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate(array_merge([
            'book_id' => 'required|exists:books,id',
            'stok_fisik' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $this->conditionValidationRules()), [
            'book_id.required' => 'Pilih buku terlebih dahulu.',
            'stok_fisik.required' => 'Stok fisik wajib diisi.',
            'stok_fisik.min' => 'Stok fisik tidak boleh negatif.',
            'foto.image' => 'File bukti harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $book = Book::findOrFail($validated['book_id']);

        $hasPending = StockOpname::where('book_id', $book->id)
            ->where('status', 'menunggu_validasi')
            ->exists();

        if ($hasPending) {
            return back()->withInput()->with('error', 'Buku ini masih memiliki pengajuan opname yang menunggu validasi.');
        }

        $fotoPath = null;
        if ($request->hasFile('foto')) {
            $fotoPath = $request->file('foto')->store('stock-opname', 'public');
        }

        StockOpname::create(array_merge($this->buildOpnamePayload($book, $validated), [
            'book_id' => $book->id,
            'user_id' => Auth::id(),
            'foto_path' => $fotoPath,
            'status' => 'menunggu_validasi',
            'submitted_at' => now(),
        ]));

        return redirect()->route('admin.stock-opname.index')
            ->with('success', 'Pengajuan stok opname berhasil dikirim ke Kepala Perpustakaan.');
    }

    public function show(StockOpname $stockOpname)
    {
        $stockOpname->load(['book.category', 'book.rak', 'user', 'validator']);

        return view('stock-opname.show', compact('stockOpname'));
    }

    public function edit(StockOpname $stockOpname)
    {
        if (! $stockOpname->canEdit()) {
            return redirect()->route('admin.stock-opname.index')
                ->with('error', 'Pengajuan ini tidak dapat diperbaiki.');
        }

        $stockOpname->load(['book.category', 'book.rak']);

        $persenRusak = $this->damagePercents();

        return view('stock-opname.edit', compact('stockOpname', 'persenRusak'));
    }

    public function update(Request $request, StockOpname $stockOpname)
    {
        if (! $stockOpname->canEdit()) {
            return redirect()->route('admin.stock-opname.index')
                ->with('error', 'Pengajuan ini tidak dapat diperbaiki.');
        }

        $validated = $request->validate(array_merge([
            'stok_fisik' => 'required|integer|min:0',
            'catatan' => 'nullable|string|max:1000',
            'foto' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ], $this->conditionValidationRules()), [
            'stok_fisik.required' => 'Stok fisik wajib diisi.',
            'stok_fisik.min' => 'Stok fisik tidak boleh negatif.',
            'foto.image' => 'File bukti harus berupa gambar.',
            'foto.max' => 'Ukuran foto maksimal 2 MB.',
        ]);

        $book = $stockOpname->book;

        if ($request->hasFile('foto')) {
            if ($stockOpname->foto_path) {
                Storage::disk('public')->delete($stockOpname->foto_path);
            }
            $stockOpname->foto_path = $request->file('foto')->store('stock-opname', 'public');
        }

        $stockOpname->update(array_merge($this->buildOpnamePayload($book, $validated), [
            'status' => 'menunggu_validasi',
            'rejection_reason' => null,
            'validated_by' => null,
            'validated_at' => null,
            'resubmitted_at' => now(),
            'revision_count' => $stockOpname->revision_count + 1,
        ]));

        return redirect()->route('admin.stock-opname.index')
            ->with('success', 'Pengajuan opname berhasil diperbaiki dan dikirim ulang.');
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

    public function bookSnapshot(Request $request)
    {
        $book = Book::findOrFail($request->book_id);

        return response()->json(array_merge($this->mapBookForForm($book), [
            'has_pending' => StockOpname::where('book_id', $book->id)
                ->where('status', 'menunggu_validasi')
                ->exists(),
        ]));
    }
}
