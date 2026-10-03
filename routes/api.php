<?php

use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Student;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;

/*
|--------------------------------------------------------------------------
| API Routes - Perpustakaan SMK Budi Mulia Ciledug
|--------------------------------------------------------------------------
*/

// Rute Login untuk Siswa
Route::post('/login', function (Request $request) {
    // Validasi input sederhana
    $request->validate([
        'nis' => 'required',
        'password' => 'required',
    ]);

    // Mencari siswa berdasarkan NIS
    $student = Student::where('nis', $request->nis)->first();

    // Verifikasi keberadaan siswa dan kecocokan password yang di-hash
    if (!$student || !Hash::check($request->password, $student->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'NIS atau Password salah!'
        ], 401);
    }

    return response()->json([
        'status' => 'success',
        'message' => 'Login Berhasil!',
        'data' => $student
    ], 200);
});

// Rute Dashboard untuk mengambil data peminjaman aktif
Route::get('/dashboard/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();

    if (!$student) {
        return response()->json([
            'status' => 'error',
            'message' => 'Siswa tidak ditemukan'
        ], 404);
    }

    // Mengambil buku yang sedang dipinjam (return_date masih null)
    $borrowedBooks = Borrowing::with(['borrowingDetails.book'])
        ->where('student_id', $student->id)
        ->whereNull('return_date')
        ->get();

    // Hitung buku yang terlambat: Belum kembali DAN sudah lewat tanggal jatuh tempo
    $lateCount = $borrowedBooks->filter(function($b) {
        return \Carbon\Carbon::parse($b->due_date)->isPast() && !$b->due_date->isToday();
    })->count();

    // Hitung sisa hari: Cari yang paling dekat tapi BELUM terlambat
    $nearestDue = $borrowedBooks->filter(function($b) {
        return !\Carbon\Carbon::parse($b->due_date)->isPast() || \Carbon\Carbon::parse($b->due_date)->isToday();
    })->min('due_date');

    $daysLeft = 0;
    if ($nearestDue) {
        $daysLeft = now()->startOfDay()->diffInDays(\Carbon\Carbon::parse($nearestDue)->startOfDay(), false);
        $daysLeft = $daysLeft < 0 ? 0 : (int)$daysLeft;
    }

    $favoriteCount = DB::table('favorites')->where('student_id', $student->id)->count();

    // Data ringkasan untuk UI Dashboard
    $summary = [
        'dipinjam' => $borrowedBooks->count(),
        'hari_lagi' => (int)$daysLeft,
        'terlambat' => $lateCount,
        'favorit' => $favoriteCount,
    ];

    return response()->json([
        'status' => 'success',
        'student' => $student,
        'summary' => $summary,
        'borrowed_books' => $borrowedBooks->map(function($b) {
            $firstDetail = $b->borrowingDetails->first();
            $isLate = $b->due_date->isPast();
            return [
                'title' => $firstDetail->book->title ?? 'Buku Tanpa Judul',
                'author' => $firstDetail->book->author ?? 'Anonim',
                'status' => $isLate ? 'Terlambat' : 'Sedang Dipinjam',
                'due_date' => $b->due_date ? $b->due_date->format('d M Y') : '-',
                'cover_image' => $firstDetail->book->cover_image ?? '',
                'is_late' => $isLate
            ];
        }),
    ]);
});

// Rute Riwayat Peminjaman
Route::get('/riwayat/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();
    if (!$student) return response()->json(['message' => 'Siswa tidak ditemukan'], 404);

    $history = DB::table('borrowings')
        ->join('borrowing_details', 'borrowings.id', '=', 'borrowing_details.borrowing_id')
        ->join('books', 'borrowing_details.book_id', '=', 'books.id')
        ->where('borrowings.student_id', $student->id)
        ->select(
            'books.title',
            'books.author',
            'books.cover_image',
            'borrowings.borrow_date',
            'borrowings.due_date',
            'borrowings.return_date',
            'borrowings.status'
        )
        ->orderBy('borrowings.borrow_date', 'desc')
        ->get();

    return response()->json([
        'status' => 'success',
        'data' => $history->map(function($b) {
            $isLate = false;
            if (!$b->return_date && \Carbon\Carbon::parse($b->due_date)->isPast()) {
                $isLate = true;
            }
            return [
                'title' => $b->title ?? 'Buku Tanpa Judul',
                'author' => $b->author ?? 'Anonim',
                'borrow_date' => \Carbon\Carbon::parse($b->borrow_date)->format('d M Y'),
                'due_date' => \Carbon\Carbon::parse($b->due_date)->format('d M Y'),
                'return_date' => $b->return_date ? \Carbon\Carbon::parse($b->return_date)->format('d M Y') : 'Belum Kembali',
                'status' => $b->status,
                'is_late' => $isLate,
                'cover_image' => $b->cover_image ?? '',
            ];
        })
    ]);
});

// Mengambil seluruh daftar buku atau melakukan pencarian dan filter kategori
Route::get('/daftar-buku', function (Request $request) {
    $search = $request->query('search');
    $categoryId = $request->query('category_id');
    $status = $request->query('status');
    $startYear = $request->query('start_year');
    $endYear = $request->query('end_year');
    $sortBy = $request->query('sort_by');

    // Tambahkan perhitungan buku yang sedang dipinjam
    $query = Book::with(['category', 'rak'])
        ->withCount(['borrowingDetails as dipinjam_count' => function($q) {
            $q->whereHas('borrowing', function($query) {
                $query->whereIn('status', ['borrowed', 'late'])
                  ->whereNull('return_date');
            });
        }]);

    // Filter Pencarian
    if ($search) {
        $cleanSearch = str_replace('-', '', $search);
        $query->where(function($q) use ($search, $cleanSearch) {
            $q->where('title', 'like', "%$search%")
              ->orWhere('author', 'like', "%$search%")
              ->orWhereRaw("REPLACE(isbn, '-', '') LIKE ?", ["%$cleanSearch%"])
              ->orWhereRaw("REPLACE(book_code, '-', '') LIKE ?", ["%$cleanSearch%"]);
        });
    }

    // Filter Kategori
    if ($categoryId && $categoryId != '0') {
        $query->where('category_id', $categoryId);
    }

    // Filter Rentang Tahun Terbit
    if ($startYear) {
        $query->where('publication_year', '>=', $startYear);
    }
    if ($endYear) {
        $query->where('publication_year', '<=', $endYear);
    }

    // Sorting
    if ($sortBy == 'Terbaru') {
        $query->orderBy('created_at', 'desc');
    } elseif ($sortBy == 'Judul A-Z') {
        $query->orderBy('title', 'asc');
    } elseif ($sortBy == 'Terpopuler') {
        // Asumsi terpopuler berdasarkan jumlah dipinjam terbanyak (bisa dikembangkan)
        $query->orderBy('stock', 'desc');
    }

    // Eksekusi query
    $books = $query->get()->map(function($book) {
        $tersedia = $book->stock - $book->dipinjam_count;
        $book->tersedia = $tersedia;
        $book->status = $tersedia > 0 ? 'Tersedia' : 'Habis';
        return $book;
    });

    // Filter Status (setelah dihitung ketersediaannya)
    if ($status && $status != 'Semua') {
        $books = $books->filter(function($book) use ($status) {
            if ($status == 'Tersedia') return $book->tersedia > 0;
            if ($status == 'Dipinjam' || $status == 'Habis') return $book->tersedia <= 0;
            return true;
        })->values();
    }

    return response()->json([
        'status' => 'success',
        'data' => $books
    ]);
});

// Mengambil daftar buku favorit milik siswa
Route::get('/favorit/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();

    if (!$student) {
        return response()->json([
            'status' => 'error',
            'message' => 'Siswa tidak ditemukan'
        ], 404);
    }

    // Ambil data buku favorit beserta perhitungan yang sedang dipinjam
    $favorites = Book::whereHas('favoritedBy', function($q) use ($student) {
        $q->where('student_id', $student->id);
    })
    ->with(['category', 'rak'])
    ->withCount(['borrowingDetails as dipinjam_count' => function($q) {
        $q->whereHas('borrowing', function($query) {
            $query->whereIn('status', ['borrowed', 'late'])
              ->whereNull('return_date');
        });
    }])
    ->get()
    ->map(function($book) {
        $tersedia = $book->stock - $book->dipinjam_count;
        $book->tersedia = $tersedia;
        $book->status = $tersedia > 0 ? 'Tersedia' : 'Habis';
        return $book;
    });

    return response()->json([
        'status' => 'success',
        'data' => $favorites
    ]);
});

// Menambah atau menghapus buku dari favorit (Toggle)
Route::post('/favorit/add', function (Request $request) {
    $request->validate([
        'nis' => 'required',
        'book_code' => 'required',
    ]);

    $student = Student::where('nis', $request->nis)->first();

    // Cari buku berdasarkan kode buku (menghapus tanda minus agar pencarian fleksibel)
    $cleanCode = str_replace('-', '', $request->book_code);
    $book = Book::whereRaw("REPLACE(book_code, '-', '') = ?", [$cleanCode])->first();

    if (!$student || !$book) {
        return response()->json([
            'status' => 'error',
            'message' => 'Siswa atau Buku tidak ditemukan'
        ], 404);
    }

    // Cek apakah buku ini sudah difavoritkan sebelumnya
    $isExist = DB::table('favorites')
                ->where('student_id', $student->id)
                ->where('book_id', $book->id)
                ->exists();

    if ($isExist) {
        // Jika sudah ada, maka hapus (Unfavorite)
        DB::table('favorites')
            ->where('student_id', $student->id)
            ->where('book_id', $book->id)
            ->delete();

        return response()->json([
            'status' => 'success',
            'message' => 'Buku berhasil dihapus dari favorit',
            'is_favorited' => false
        ]);
    } else {
        // Jika belum ada, maka tambahkan (Favorite)
        DB::table('favorites')->insert([
            'student_id' => $student->id,
            'book_id' => $book->id,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json([
            'status' => 'success',
            'message' => 'Buku berhasil ditambahkan ke favorit',
            'is_favorited' => true
        ]);
    }
});

// Mengambil daftar kategori
Route::get('/kategori', function () {
    return response()->json([
        'status' => 'success',
        'data' => \App\Models\Category::where('status', 'Aktif')->get()
    ]);
});

// Rute Peminjaman Buku via QR Code
Route::post('/pinjam-buku', function (Request $request) {
    $request->validate([
        'nis' => 'required',
        'book_code' => 'required',
    ]);

    $student = Student::where('nis', $request->nis)->first();

    $cleanCode = str_replace('-', '', $request->book_code);
    $book = Book::whereRaw("REPLACE(book_code, '-', '') = ?", [$cleanCode])
                ->orWhereRaw("REPLACE(isbn, '-', '') = ?", [$cleanCode])
                ->first();

    if (!$student || !$book) {
        return response()->json([
            'status' => 'error',
            'message' => 'Siswa atau Buku tidak ditemukan'
        ], 404);
    }

    // MENGHITUNG KETERSEDIAAN BUKU SAAT INI SECARA REAL-TIME
    $buku_dipinjam = \App\Models\BorrowingDetail::where('book_id', $book->id)
        ->whereHas('borrowing', function($q) {
            $q->whereIn('status', ['borrowed', 'late'])
              ->whereNull('return_date');
        })->count();

    $tersedia = $book->stock - $buku_dipinjam;

    // Tolak peminjaman jika nilai tersedia sudah habis (0)
    if ($tersedia <= 0) {
        return response()->json([
            'status' => 'error',
            'message' => 'Maaf, semua eksemplar buku ini sedang dipinjam oleh siswa lain.'
        ], 400);
    }

    DB::beginTransaction();
    try {
        // 1. Buat header transaksi di tabel borrowings
        $borrowing = Borrowing::create([
            'transaction_code' => 'TRX-' . time(),
            'student_id' => $student->id,
            // book_id dihapus dari sini karena tidak ada di tabel borrowings
            'borrow_date' => now(),
            'due_date' => now()->addDays(7),
            'status' => 'borrowed',
        ]);

        // 2. Simpan detail buku yang dipinjam ke tabel borrowing_details
        \App\Models\BorrowingDetail::create([
            'borrowing_id' => $borrowing->id,
            'book_id' => $book->id,
            'qty' => 1,
            'status' => 'borrowed'
        ]);

        DB::commit();

        return response()->json([
            'status' => 'success',
            'message' => 'Berhasil meminjam buku: ' . $book->title,
            'data' => $borrowing
        ]);

    } catch (\Exception $e) {
        DB::rollBack();
        return response()->json([
            'status' => 'error',
            'message' => 'Terjadi kesalahan sistem saat memproses transaksi.'
        ], 500);
    }
});

// Rute Notifikasi untuk Siswa
Route::get('/notifikasi/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();
    if (!$student) return response()->json(['message' => 'Siswa tidak ditemukan'], 404);

    $notifications = [];

    // 1. Ambil Peminjaman Terlambat (Penting)
    $allActiveBorrowings = Borrowing::with(['borrowingDetails.book'])
        ->where('student_id', $student->id)
        ->whereNull('return_date')
        ->get();

    foreach ($allActiveBorrowings as $b) {
        $firstDetail = $b->borrowingDetails->first();
        $isLate = \Carbon\Carbon::parse($b->due_date)->isPast() && !$b->due_date->isToday();
        $isDueSoon = \Carbon\Carbon::parse($b->due_date)->isToday() || (now()->diffInDays($b->due_date, false) == 1);

        if ($isLate) {
            $notifications[] = [
                'type' => 'urgent',
                'category' => 'Keterlambatan',
                'title' => 'Buku terlambat dikembalikan',
                'message' => "Buku \"" . ($firstDetail->book->title ?? 'Buku') . "\" telah melewati tanggal jatuh tempo (" . $b->due_date->format('d M Y') . "). Segera kembalikan ke perpustakaan.",
                'time' => $b->due_date->diffForHumans(),
                'color' => 'red',
                'icon' => 'warning_rounded'
            ];
        } elseif ($isDueSoon) {
            $notifications[] = [
                'type' => 'urgent',
                'category' => 'Jatuh Tempo',
                'title' => 'Buku mendekati jatuh tempo',
                'message' => "Buku \"" . ($firstDetail->book->title ?? 'Buku') . "\" harus dikembalikan " . ($b->due_date->isToday() ? 'HARI INI' : 'besok') . ".",
                'time' => 'Penting',
                'color' => 'orange',
                'icon' => 'error_outline'
            ];
        }
    }

    // 2. Ambil Denda Belum Lunas (Penting)
    $unpaidFines = \App\Models\Fine::whereHas('borrowing', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })
            ->where('fine_status', 'unpaid')
        ->get();

    foreach ($unpaidFines as $f) {
        $notifications[] = [
            'type' => 'urgent',
            'category' => 'Denda Belum Dibayar',
            'title' => 'Denda belum dibayar',
            'message' => "Anda memiliki denda sebesar Rp " . number_format($f->total_fine, 0, ',', '.') . " yang belum dibayar.",
            'time' => $f->updated_at->diffForHumans(),
            'color' => 'orange',
            'icon' => 'wallet_giftcard_rounded'
        ];
    }

    // 3. Ambil Peminjaman Baru (Informasi)
    $recentBorrowings = Borrowing::with('book')
        ->where('student_id', $student->id)
        ->where('status', 'borrowed')
        ->orderBy('borrow_date', 'desc')
        ->take(3)
        ->get();

    foreach ($recentBorrowings as $b) {
        $notifications[] = [
            'type' => 'info',
            'category' => 'Peminjaman',
            'title' => 'Peminjaman Berhasil',
            'message' => "Anda berhasil meminjam buku \"" . ($b->book->title ?? 'Buku') . "\". Jangan lupa kembalikan sebelum " . $b->due_date->format('d M Y') . ".",
            'time' => $b->borrow_date->diffForHumans(),
            'color' => 'blue',
            'icon' => 'notifications_none_rounded'
        ];
    }

    return response()->json([
        'status' => 'success',
        'data' => $notifications
    ]);
});

// Rute Profil untuk Akun Saya
Route::get('/profile/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();
    if (!$student) return response()->json(['message' => 'Siswa tidak ditemukan'], 404);

    $borrowedCount = Borrowing::where('student_id', $student->id)->whereNull('return_date')->count();
    $lateCount = Borrowing::where('student_id', $student->id)
        ->whereNull('return_date')
        ->where('due_date', '<', now())
        ->count();

    $totalFine = \App\Models\Fine::whereHas('borrowing', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })
            ->where('fine_status', 'unpaid')
        ->sum('total_fine');

    return response()->json([
        'status' => 'success',
        'student' => $student,
        'stats' => [
            'dipinjam' => $borrowedCount,
            'terlambat' => $lateCount,
            'denda' => $totalFine,
        ]
    ]);
});

// Rute Denda & Pembayaran
Route::get('/fines/{nis}', function ($nis) {
    $student = Student::where('nis', $nis)->first();
    if (!$student) return response()->json(['message' => 'Siswa tidak ditemukan'], 404);

    $fines = \App\Models\Fine::with(['borrowing.borrowingDetails.book'])
        ->whereHas('borrowing', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })
        ->orderBy('created_at', 'desc')
        ->get();

    $summary = [
        'unpaid' => (int)$fines->where('fine_status', 'unpaid')->sum('total_fine'),
        'processing' => (int)$fines->where('fine_status', 'processing')->sum('total_fine'),
        'paid' => (int)$fines->where('fine_status', 'paid')->sum('total_fine'),
        'unpaid_count' => $fines->where('fine_status', 'unpaid')->count(),
        'processing_count' => $fines->where('fine_status', 'processing')->count(),
        'paid_count' => $fines->where('fine_status', 'paid')->count(),
    ];

    $data = $fines->map(function($f) {
        // Ambil detail buku pertama dari peminjaman terkait
        $firstDetail = $f->borrowing->borrowingDetails->first();
        $bookTitle = $firstDetail && $firstDetail->book ? $firstDetail->book->title : 'Buku';
        $coverImage = $firstDetail && $firstDetail->book ? $firstDetail->book->cover_image : '';

        $lateDays = 0;
        if ($f->borrowing && $f->borrowing->due_date && $f->borrowing->return_date) {
            $lateDays = $f->borrowing->due_date->diffInDays($f->borrowing->return_date, false);
        } elseif ($f->borrowing && $f->borrowing->due_date && $f->borrowing->status == 'late') {
             $lateDays = $f->borrowing->due_date->diffInDays(now(), false);
        }
        $lateDays = $lateDays < 0 ? 0 : (int)$lateDays;

        return [
            'id' => $f->id,
            'book_title' => $bookTitle,
            'cover_image' => $coverImage,
            'amount' => (int)$f->total_fine,
            'status' => $f->fine_status,
            'late_days' => $lateDays,
            'due_date' => $f->borrowing->due_date ? $f->borrowing->due_date->format('d M Y') : '-',
            'date' => $f->created_at->format('d M Y, H:i') . ' WIB',
            'type' => 'Pembayaran Denda',
        ];
    });

    return response()->json([
        'status' => 'success',
        'summary' => $summary,
        'data' => $data
    ]);
});

// Update Profil Siswa
Route::post('/profile/update', function (Request $request) {
    $request->validate([
        'nis' => 'required',
        'name' => 'required|string|max:255',
        'phone' => 'nullable|string|max:20',
        'address' => 'nullable|string',
        'gender' => 'nullable|string',
        'class' => 'nullable|string',
        'major' => 'nullable|string',
        'pob' => 'nullable|string',
        'tempat_lahir' => 'nullable|string',
        'dob' => 'nullable',
        'tanggal_lahir' => 'nullable',
        'photo' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
    ]);

    $student = Student::where('nis', $request->nis)->first();
    if (!$student) return response()->json(['message' => 'Siswa tidak ditemukan'], 404);

    $data = [
        'name' => $request->name,
        'phone' => $request->phone,
        'address' => $request->address,
        'gender' => $request->gender,
        'class' => $request->class,
        'major' => $request->major,
        'pob' => $request->pob ?? $request->tempat_lahir,
        'dob' => $request->dob ?? $request->tanggal_lahir,
    ];

    // Handle Photo Upload
    if ($request->hasFile('photo')) {
        $photoDir = public_path('photos');

        // Create directory if not exists
        if (!file_exists($photoDir)) {
            mkdir($photoDir, 0777, true);
        }

        // Delete old photo if exists
        if ($student->photo && file_exists($photoDir . '/' . $student->photo)) {
            unlink($photoDir . '/' . $student->photo);
        }

        $file = $request->file('photo');
        $filename = time() . '_' . $student->nis . '.' . $file->getClientOriginalExtension();
        $file->move($photoDir, $filename);
        $data['photo'] = $filename;
    }

    $student->update($data);

    return response()->json([
        'status' => 'success',
        'message' => 'Profil berhasil diperbarui!',
        'data' => $student
    ]);
});

// Update Password Siswa
Route::post('/password/update', function (Request $request) {
    $request->validate([
        'nis' => 'required',
        'old_password' => 'required',
        'new_password' => 'required|min:6',
    ]);

    $student = Student::where('nis', $request->nis)->first();
    if (!$student || !Hash::check($request->old_password, $student->password)) {
        return response()->json([
            'status' => 'error',
            'message' => 'Password lama salah!'
        ], 401);
    }

    $student->update([
        'password' => Hash::make($request->new_password)
    ]);

    return response()->json([
        'status' => 'success',
        'message' => 'Password berhasil diperbarui!'
    ]);
});

// Route Bantuan untuk update password siswa ke format Hash (Bcrypt)
Route::get('/fix-password/{nis}/{new_password}', function ($nis, $new_password) {
    $student = Student::where('nis', $nis)->first();
    if ($student) {
        $student->password = Hash::make($new_password);
        $student->save();
        return response()->json([
            'message' => "Password siswa $student->name berhasil diperbarui."
        ]);
    }
    return response()->json(['message' => "Siswa tidak ditemukan"], 404);
});

// Rute Pengumuman untuk Siswa
Route::get('/informasi', function () {
    return response()->json([
        'status' => 'success',
        'data' => \App\Models\Announcement::latest()->get()
    ]);
});
