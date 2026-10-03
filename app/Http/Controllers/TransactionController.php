<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Book;
use App\Models\Student;
use App\Models\BorrowingDetail;
use App\Models\FineSetting;
use App\Models\Fine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Barryvdh\DomPDF\Facade\Pdf;
use App\Exports\ReportExport;
use Maatwebsite\Excel\Facades\Excel;
use App\Services\FineCalculator;
use App\Support\ClassLoanQuota;
use App\Models\Category;
use Illuminate\Support\Facades\Schema;

class TransactionController extends Controller
{
    public function index(Request $request)
    {
        $query = Borrowing::with(['student', 'details.book.category', 'fine'])
                    ->withCount('details'); 

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->whereHas('student', function($sq) use ($search) {
                    $sq->where('name', 'like', "%{$search}%")
                    ->orWhere('nis', 'like', "%{$search}%");
                })
                ->orWhereHas('details.book', function($bq) use ($search) {
                    $bq->where('title', 'like', "%{$search}%");
                })
                ->orWhere('transaction_code', 'like', "%{$search}%");
            });
        }

        if ($request->status == 'dipinjam') {
            $query->where('status', 'borrowed');
        } elseif ($request->status == 'terlambat') {
            $query->where('status', 'borrowed')
                ->where('due_date', '<', now());
        } elseif ($request->status == 'kembali') {
            $query->where('status', 'returned');
        }

        $total_peminjaman = Borrowing::count();
        $peminjaman_aktif = Borrowing::where('status', 'borrowed')->count();
        $total_pengembalian = Borrowing::where('status', 'returned')->count();
        $total_terlambat = Borrowing::where('status', 'borrowed')
                            ->where('due_date', '<', now())
                            ->count();

        $persen_aktif = $total_peminjaman > 0 ? round(($peminjaman_aktif / $total_peminjaman) * 100, 1) : 0;
        $persen_kembali = $total_peminjaman > 0 ? round(($total_pengembalian / $total_peminjaman) * 100, 1) : 0;
        $persen_terlambat = $total_peminjaman > 0 ? round(($total_terlambat / $total_peminjaman) * 100, 1) : 0;
        
        $transactions = $query->orderBy('created_at', 'desc')->paginate(10);
        $transactions->appends($request->all());

        $students = Student::orderBy('name', 'asc')->get();
        $books = Book::with('category')->where('stock', '>', 0)->orderBy('title', 'asc')->get();
        $classLoanRules = ClassLoanQuota::mapForForm();
        
        $selectedTrx = $transactions->first();

        return view('transactions.index', compact(
            'transactions', 
            'students', 
            'books',
            'classLoanRules', 
            'total_peminjaman', 
            'peminjaman_aktif', 
            'total_pengembalian', 
            'total_terlambat',
            'persen_aktif',
            'persen_kembali',
            'persen_terlambat',
            'selectedTrx'
        ));
    }

    public function create()
    {
        return redirect()->route('admin.transactions.index');
    }

    public function edit(Borrowing $borrowing)
    {
        $borrowing->load(['student', 'details.book']);
        return view('transactions.edit', compact('borrowing'));
    }

    public function update(Request $request, Borrowing $borrowing)
    {
        $borrowing->load(['student', 'details.book.category']);
        $loanDays = Category::maxLoanDaysForBooks($borrowing->details->pluck('book')->filter());
        $maxDue = $borrowing->borrow_date->copy()->addDays($loanDays)->toDateString();

        $request->validate([
            'due_date' => [
                'required',
                'date',
                'after_or_equal:' . $borrowing->borrow_date->toDateString(),
                'before_or_equal:' . $maxDue,
            ],
        ], [
            'due_date.before_or_equal' => "Durasi peminjaman maksimal {$loanDays} hari sesuai kategori buku.",
            'due_date.after_or_equal' => 'Tanggal jatuh tempo tidak boleh sebelum tanggal pinjam.',
        ]);

        $borrowing->update([
            'due_date' => $request->due_date
        ]);

        return redirect()->route('admin.transactions.index')->with('success', 'Data transaksi berhasil diperbarui.');
    }
    
    public function returnIndex(Request $request)
    {
        $total_peminjaman = Borrowing::count();
        $peminjaman_aktif = Borrowing::where('status', 'borrowed')->count();
        $total_pengembalian = Borrowing::where('status', 'returned')->count();
        $total_terlambat = Borrowing::where('status', 'borrowed')
                            ->where('due_date', '<', now()->startOfDay())
                            ->count();
        
        $persen_aktif = $total_peminjaman > 0 ? round(($peminjaman_aktif / $total_peminjaman) * 100, 1) : 0;
        $persen_kembali = $total_peminjaman > 0 ? round(($total_pengembalian / $total_peminjaman) * 100, 1) : 0;
        $persen_terlambat = $total_peminjaman > 0 ? round(($total_terlambat / $total_peminjaman) * 100, 1) : 0;
        
        $student = null;
        $activeBorrowsCount = 0;
        $lateBorrowsCount = 0;
        $activeTransactions = collect();
        $alpineBooks = [];

        $pengaturanDenda = FineCalculator::activeSetting();
                        
        $dendaTerlambat  = (int) ($pengaturanDenda?->late_fee_per_day ?? 1000);
        $persenRusakRingan = (int) ($pengaturanDenda?->damage_light_percent ?? 25);
        $persenRusakSedang = (int) ($pengaturanDenda?->damage_medium_percent ?? 50);
        $persenRusakBerat  = (int) ($pengaturanDenda?->damage_heavy_percent ?? 75);

        if ($request->filled('search')) {
            $search = $request->search;
            $student = Student::where('nis', $search)
                            ->orWhere('name', 'like', "%{$search}%")
                            ->first();

            if ($student) {
                $activeBorrowsCount = Borrowing::where('student_id', $student->id)
                                            ->where('status', 'borrowed')
                                            ->count();
                                            
                $lateBorrowsCount = Borrowing::where('student_id', $student->id)
                                            ->where('status', 'borrowed')
                                            ->where('due_date', '<', now()->startOfDay())
                                            ->count();
                                            
                $activeTransactions = Borrowing::with('details.book')
                                            ->where('student_id', $student->id)
                                            ->where('status', 'borrowed')
                                            ->get();
            }
        }

        $historyTransactions = Borrowing::with(['student', 'details.book', 'fine'])
                                        ->where('status', 'returned')
                                        ->orderBy('return_date', 'desc')
                                        ->paginate(10)
                                        ->withQueryString();

        // Pemetaan nilai denda untuk tampilan riwayat agar tidak Rp 0
        $historyTransactions->getCollection()->transform(function ($trx) {
            $trx->total_fine = $trx->fine ? $trx->fine->total_fine : 0;
            return $trx;
        });

        if ($student && $activeTransactions->count() > 0) {
            foreach ($activeTransactions as $trx) {
                foreach ($trx->details as $detail) {
                    $dueDate = Carbon::parse($trx->due_date)->startOfDay();
                    $today = now()->startOfDay();
                    
                    $lateDays = $today->isAfter($dueDate) ? intval($today->diffInDays($dueDate)) : 0;
                    
                    $words = explode(' ', trim($detail->book->title ?? 'Buku'));
                    $initials = strtoupper(substr($words[0] ?? 'B', 0, 1) . substr($words[1] ?? '', 0, 1));

                    $alpineBooks[] = [
                        'detail_id'   => $detail->id,
                        'borrowing_id'=> $trx->id,
                        'title'       => $detail->book->title ?? 'Buku Tidak Diketahui',
                        'isbn'        => $detail->book->isbn ?? '-',
                        'initials'    => $initials ?: 'BK',
                        'borrow_date' => Carbon::parse($trx->borrow_date)->format('d M Y'),
                        'due_date'    => $dueDate->format('d M Y'),
                        'is_late'     => $lateDays > 0,
                        'late_days'   => $lateDays,
                        'late_fee'    => $lateDays * $dendaTerlambat, 
                        'harga_buku'  => FineCalculator::bookPrice($detail->book),
                        'condition'   => 'Baik',
                        'damage_level'=> 'sedang',
                        'selected'    => true
                    ];
                }
            }
        }

        return view('transactions.returns_list', compact(
            'student', 
            'activeBorrowsCount', 
            'lateBorrowsCount', 
            'activeTransactions',
            'historyTransactions',
            'alpineBooks',
            'dendaTerlambat', 
            'persenRusakRingan',
            'persenRusakSedang',
            'persenRusakBerat',
            'total_peminjaman',     
            'peminjaman_aktif',    
            'total_pengembalian',   
            'total_terlambat',      
            'persen_aktif',         
            'persen_kembali',       
            'persen_terlambat'      
        ));
    }

   public function processReturn(Request $request)
    {
        $dataDikembalikan = [];
        if ($request->has('returns')) {
            foreach ($request->returns as $item) {
                if (isset($item['detail_id'])) {
                    $dataDikembalikan[] = $item;
                }
            }
        }

        if (empty($dataDikembalikan)) {
            return back()->with('error', 'Silakan centang minimal 1 buku yang ingin dikembalikan.');
        }

        try {
            DB::transaction(function () use ($dataDikembalikan, $request) {
                $borrowingIdsUntukDicek = [];
                $setting = FineCalculator::activeSetting();

                foreach ($dataDikembalikan as $returnItem) {
                    $detail = BorrowingDetail::with(['borrowing', 'book'])->find($returnItem['detail_id']);
                    if (!$detail || !$detail->book) continue;

                    $kondisi = $returnItem['condition'] ?? 'Baik';
                    $damageLevel = $returnItem['damage_level'] ?? 'sedang';
                    $lateDays = isset($returnItem['late_days']) ? (int) $returnItem['late_days'] : 0;
                    $isLate = $lateDays > 0;

                    $calc = FineCalculator::forReturn(
                        $detail->book,
                        $kondisi,
                        $damageLevel,
                        $lateDays,
                        $setting
                    );

                    $detail->status = 'returned';
                    $detail->return_date = now();
                    $detail->condition = $kondisi;
                    $detail->damage_level = $calc['damage_level'];
                    $detail->damage_percent = $calc['damage_percent'];
                    $detail->book_price_snapshot = $calc['book_price'];
                    $detail->late_fine = $calc['late_fine'];
                    $detail->condition_fine = $calc['condition_fine'];
                    $detail->save();

                    $book = $detail->book;
                    $kondisiLower = strtolower($kondisi);
                    $tingkatRusak = strtolower($calc['damage_level'] ?? $damageLevel ?: 'sedang');

                    if ($kondisiLower === 'hilang') {
                        $book->increment('jumlah_hilang', 1);
                    } elseif ($kondisiLower === 'rusak') {
                        $book->increment('jumlah_rusak', 1);
                        $levelCol = match ($tingkatRusak) {
                            'ringan' => 'jumlah_rusak_ringan',
                            'berat' => 'jumlah_rusak_berat',
                            default => 'jumlah_rusak_sedang',
                        };
                        if (Schema::hasColumn('books', $levelCol)) {
                            $book->increment($levelCol, 1);
                        }
                        $book->increment('stock', 1);
                    } else {
                        $book->increment('stock', 1);
                    }

                    $book->refresh();
                    $book->update([
                        'stock_status' => $book->derivedStockStatus(),
                        'last_stock_take_at' => now(),
                        'verified_by' => auth()->id(),
                    ]);

                    $borrowing = $detail->borrowing;
                    $totalFine = $calc['total_fine'];

                    if ($totalFine > 0) {
                        $fine = Fine::firstOrNew(['borrowing_id' => $borrowing->id]);
                        $fine->total_fine = ($fine->total_fine ?? 0) + $totalFine;
                        $fine->denda_dibayar = $fine->denda_dibayar ?? 0;
                        $fine->fine_status = 'unpaid';
                        
                        $alasan = [];
                        if ($isLate) {
                            $alasan[] = "Terlambat {$lateDays} hari";
                        }
                        if (strtolower($kondisi) === 'rusak') {
                            $levelLabel = ucfirst($calc['damage_level'] ?? 'sedang');
                            $alasan[] = "Rusak {$levelLabel} {$calc['damage_percent']}% dari harga Rp " . number_format($calc['book_price'], 0, ',', '.');
                        } elseif (strtolower($kondisi) === 'hilang') {
                            $alasan[] = "Hilang 100% harga buku Rp " . number_format($calc['book_price'], 0, ',', '.');
                        }
                        
                        $ketBaru = "{$detail->book->title} (" . implode(', ', $alasan) . ")";
                        if (empty($fine->keterangan)) {
                            $fine->keterangan = "Denda buku: " . $ketBaru;
                        } elseif (!str_contains($fine->keterangan, $ketBaru)) {
                            $fine->keterangan .= ", " . $ketBaru;
                        }
                        $fine->save();
                    }

                    $borrowingIdsUntukDicek[] = $borrowing->id;
                }

                foreach (array_unique($borrowingIdsUntukDicek) as $borrowingId) {
                    $borrowing = Borrowing::with('details')->find($borrowingId);
                    
                    $sisaDipinjam = $borrowing->details->where('status', 'borrowed')->count();

                    if ($sisaDipinjam === 0) {
                        $borrowing->status = 'returned';
                        $borrowing->return_date = now();
                        $borrowing->save(); 
                    }
                }
            });

            return back()->with('success', 'Buku berhasil dikembalikan!');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses: ' . $e->getMessage());
        }
    }

    public function store(Request $request)
    {
        $request->validate([
            'student_id' => 'required|exists:students,id',
        ]);

        $student = Student::findOrFail($request->student_id);

        $validated = $request->validate([
            'student_id' => 'required|exists:students,id',
            'book_id' => 'required|array|min:1',
            'book_id.*' => 'required|exists:books,id',
        ]);

        $selectedBooks = Book::with('category')->whereIn('id', $validated['book_id'])->get();
        $student->assertCanBorrowBooks($selectedBooks);
        $loanDays = Category::maxLoanDaysForBooks($selectedBooks);
        $dueDate = now()->addDays($loanDays)->toDateString();

        try {
            DB::transaction(function () use ($validated, $dueDate) {
                $transactionCode = 'TRX-' . now()->format('YmdHis') . '-' . Auth::id();

                $borrowing = Borrowing::create([
                    'transaction_code' => $transactionCode,
                    'student_id'       => $validated['student_id'],
                    'user_id'          => Auth::id(),
                    'borrow_date'      => now()->toDateString(),
                    'due_date'         => $dueDate,
                    'status'           => 'borrowed', 
                ]);

                foreach ($validated['book_id'] as $id) {
                    $book = Book::lockForUpdate()->find($id);

                    if (!$book || $book->stock < 1) {
                        throw new \Exception("Stok tidak cukup untuk buku: " . ($book->title ?? $id));
                    }

                    BorrowingDetail::create([
                        'borrowing_id' => $borrowing->id,
                        'book_id' => $id,
                        'qty' => 1,
                        'status' => 'borrowed',
                    ]);

                    $book->decrement('stock');
                }
            });

            return redirect()->route('admin.transactions.index')->with('success', 'Peminjaman berhasil dibuat.');
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal menyimpan: ' . $e->getMessage());
        }
    }

    public function returnBook(Request $request, Borrowing $borrowing)
    {
        $validated = $request->validate(['return_date' => 'nullable|date']);

        try {
            return DB::transaction(function () use ($validated, $borrowing) {
                if ($borrowing->status === 'returned') {
                    throw new \Exception('Peminjaman ini sudah dikembalikan sebelumnya.');
                }

                $borrowing->load('details.book');
                
                $returnDate = $validated['return_date'] ? Carbon::parse($validated['return_date']) : now();
                $dueDate = Carbon::parse($borrowing->due_date);

                if ($returnDate->isAfter($dueDate)) {
                    $daysOverdue = $returnDate->startOfDay()->diffInDays($dueDate->startOfDay());
                    
                    $pengaturanDenda = FineSetting::where('is_active', 1)->first() ?? FineSetting::first();
                    $tarifDenda = !empty($pengaturanDenda->late_fee_per_day) ? $pengaturanDenda->late_fee_per_day : 1000;
                    $totalFine = $daysOverdue * $tarifDenda;

                    $fine = Fine::firstOrNew(['borrowing_id' => $borrowing->id]);
                    $fine->total_fine = ($fine->total_fine ?? 0) + $totalFine;
                    $fine->denda_dibayar = $fine->denda_dibayar ?? 0;
                    $fine->fine_status = 'unpaid';
                    
                    $ketBaru = "Terlambat $daysOverdue hari";
                    if (empty($fine->keterangan)) {
                        $fine->keterangan = $ketBaru;
                    } elseif (!str_contains($fine->keterangan, $ketBaru)) {
                        $fine->keterangan .= ", " . $ketBaru;
                    }
                    $fine->save();
                }

                $borrowing->update([
                    'return_date' => $returnDate,
                    'status'      => 'returned',
                ]);

                foreach ($borrowing->details as $detail) {
                    if ($detail->book) {
                        // $detail->book->increment('stock', $detail->qty);
                    }
                }

                return response()->json([
                    'success' => true, 
                    'message' => 'Buku berhasil dikembalikan.' . (isset($totalFine) ? ' Denda otomatis tercatat.' : '')
                ], 200);
            });
        } catch (\Exception $e) {
            return response()->json(['success' => false, 'message' => $e->getMessage()], 422);
        }
    }

    public function returnPage(Borrowing $borrowing)
    {
        $borrowing->load(['student', 'details.book']);
        return view('transactions.return', compact('borrowing'));
    }

    public function history(Request $request)
    {
        $stats = [
            'total' => Borrowing::count(),
            'borrowing' => Borrowing::where('status', 'borrowed')->count(),
            'returned' => Borrowing::where('status', 'returned')->count(),
            'overdue' => Borrowing::where('due_date', '<', now())
                                            ->where('status', 'borrowed')
                                            ->count(),
        ];

        $query = Borrowing::with(['student', 'user', 'details.book', 'fine'])
                    ->withCount('details');

        if ($request->filled('search')) {
            $query->where('transaction_code', 'like', "%{$request->search}%")
                ->orWhereHas('student', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->paginate(10)->withQueryString();
        
        $transactions->getCollection()->transform(function ($trx) {
            $trx->total_fine = $trx->fine ? $trx->fine->total_fine : 0;
            return $trx;
        });
        
        $selectedTrx = $transactions->first();

        return view('transactions.history', compact('transactions', 'stats', 'selectedTrx'));
    }

    public function exportHistory(Request $request)
    {
        $format = $request->query('format', 'pdf');
        
        $query = Borrowing::with(['student', 'user', 'details.book', 'fine'])
                    ->withCount('details');

        if ($request->filled('search')) {
            $query->where('transaction_code', 'like', "%{$request->search}%")
                ->orWhereHas('student', fn($q) => $q->where('name', 'like', "%{$request->search}%"));
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $transactions = $query->orderBy('created_at', 'desc')->get();

        $transactions->transform(function ($trx) {
            $trx->total_fine = $trx->fine ? $trx->fine->total_fine : 0;
            return $trx;
        });

        if ($format === 'pdf') {
            $pdf = Pdf::loadView('admin.head.reports.pdf', [
                'transactions' => $transactions,
                'type' => 'riwayat transaksi',
                'startDate' => $transactions->min('borrow_date') ?? now(),
                'endDate' => $transactions->max('borrow_date') ?? now(),
                'headers' => ['Kode Transaksi', 'Nama Siswa', 'Tanggal Pinjam', 'Tanggal Kembali', 'Status']
            ]);
            return $pdf->download('Riwayat-Transaksi-' . date('Y-m-d') . '.pdf');
        } elseif ($format === 'excel') {
            return Excel::download(new ReportExport($transactions), 'Laporan-Riwayat-Transaksi-' . date('Y-m-d') . '.xlsx');
        }

        return back()->with('error', 'Format tidak valid!');
    }

    public function report(Request $request)
    {
        $query = Borrowing::with(['student', 'details.book', 'user', 'fine']);

        if ($request->filled('start_date') && $request->filled('end_date')) {
            $query->whereBetween('borrow_date', [$request->start_date, $request->end_date]);
        }

        $transactions = $query->latest()->get();

        $transactions->transform(function ($trx) {
            $trx->total_fine = $trx->fine ? $trx->fine->total_fine : 0;
            return $trx;
        });

        return view('transactions.report', compact('transactions'));
    }

    public function fineIndex(Request $request)
    {
        $query = Borrowing::with(['student', 'details.book', 'fine'])
            ->whereHas('fine', function ($q) {
                $q->where('total_fine', '!=', 0);
            });

        if ($request->filled('search')) {
            $search = $request->search;
            $query->whereHas('student', function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        if ($request->status === 'paid') { 
            $query->whereHas('fine', function($q) {
                $q->where('fine_status', 'paid'); 
            });
        } elseif ($request->status === 'unpaid') { 
            $query->whereHas('fine', function($q) {
                $q->where('fine_status', 'unpaid')
                  ->orWhereNull('fine_status');
            });
        }

        $fines = $query->latest()->paginate(10);
        
        return view('fines.index', compact('fines')); 
    }

    public function payFine(Request $request, Borrowing $borrowing)
    {
        $borrowing->load('fine');

        if ($borrowing->fine && abs($borrowing->fine->total_fine) > 0 && $borrowing->fine->fine_status !== 'paid') {
            
            $borrowing->fine->fine_status = 'paid';
            $borrowing->fine->denda_dibayar = abs($borrowing->fine->total_fine);
            $borrowing->fine->save();

            return back()->with('success', 'Pembayaran denda berhasil diproses!');
        }

        return back()->with('error', 'Denda tidak valid atau sudah pernah dibayar.');
    }

    public function print(Borrowing $borrowing)
    {
        $borrowing->load(['student', 'user', 'details.book', 'fine']);
        return view('transactions.receipt', compact('borrowing'));
    }

    public function pdf(Borrowing $borrowing)
    {
        $borrowing->load(['student', 'user', 'details.book', 'fine']);

        $pdf = Pdf::loadView('transactions.receipt', compact('borrowing'));
        return $pdf->download('bukti-peminjaman-' . $borrowing->transaction_code . '.pdf');
    }
}