<?php

namespace App\Http\Controllers;

use App\Models\Student;
use App\Models\Book;
use App\Models\Borrowing;
use App\Models\Favorite;
use App\Models\Category;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Exports\StudentTemplateExport;
use App\Imports\StudentsImport;
use App\Models\ImportHistory;
use Maatwebsite\Excel\Facades\Excel;

class StudentController extends Controller
{
    public function index(Request $request)
    {
        // 1. Ambil daftar Kelas dan Jurusan unik untuk isi dropdown filter
        $classes = Student::whereNotNull('class')->distinct()->orderBy('class')->pluck('class');
        $majors = Student::select('major')->distinct()->whereNotNull('major')->pluck('major');

        // 2. Hitung Statistik untuk Card di Atas
        $total_siswa = Student::count();
        $siswa_aktif = Student::where('status', 'active')->count();
        $siswa_nonaktif = Student::where('status', 'inactive', 'graduated')->count();
        $siswa_bulan_ini = Student::whereMonth('created_at', Carbon::now()->month)
                                    ->whereYear('created_at', Carbon::now()->year)
                                    ->count();

        // 3. Query Utama dengan Filter
        $query = Student::query();

        // Filter: Pencarian (Nama atau NIS)
        if ($request->filled('search')) {
            $search = $request->input('search');
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('nis', 'like', "%{$search}%");
            });
        }

        // Filter: Kelas
        if ($request->filled('class')) {
            $query->where('class', $request->class);
        }

        // Filter: Jurusan
        if ($request->filled('major')) {
            $query->where('major', $request->major);
        }

        // Filter: Status
        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        // 4. Eksekusi Query
        $students = $query->latest()
            ->paginate(10)
            ->appends($request->query());

        // 5. Hitung Persentase untuk tampilan card
        $persen_aktif = $total_siswa > 0 ? round(($siswa_aktif / $total_siswa) * 100, 1) : 0;
        $persen_nonaktif = $total_siswa > 0 ? round(($siswa_nonaktif / $total_siswa) * 100, 1) : 0;

        $lastImport = ImportHistory::latest()->first();

        return view('students.index', compact(
            'students', 'classes', 'majors', 'total_siswa', 
            'siswa_aktif', 'siswa_nonaktif', 'siswa_bulan_ini',
            'persen_aktif', 'persen_nonaktif', 'lastImport'
        ));
    }

    public function create()
    {
        return view('students.create');
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'nis'           => 'required|digits_between:1,7|unique:students,nis',
            'name'          => 'required|string|max:255',
            'class'         => 'required|in:X,XI,XII',
            'major'         => 'nullable|string|max:255',
            'pob'           => 'required|string|max:100',
            'dob'           => 'required|date',
            'gender'        => 'required|in:Laki-laki,Perempuan',
            'address'       => 'required|string',
            'phone'         => 'required|numeric',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
        ]);

        if ($request->hasFile('photo')) {
            $validated['photo'] = $request->file('photo')->store('student_photos', 'public');
        }

        $validated['status'] = 'active';
        $validated['password'] = bcrypt($request->nis);

        Student::create($validated);

        return redirect()->route('admin.students.index')->with('success', 'Siswa baru berhasil didaftarkan!');
    }

    public function edit(Student $student)
    {
        return view('students.edit', compact('student'));
    }

    /**
     * Menampilkan detail profil siswa dan riwayat peminjamannya.
     */
    public function show(Student $student)
    {
        // Ambil riwayat peminjaman siswa ini
        $borrowings = Borrowing::with(['details.book', 'user'])
            ->where('student_id', $student->id)
            ->orderBy('borrow_date', 'desc')
            ->paginate(5);

        // Hitung statistik singkat untuk profil siswa
        $stats = [
            'total_pinjam' => Borrowing::where('student_id', $student->id)->count(),
            'sedang_pinjam' => Borrowing::where('student_id', $student->id)->where('status', 'borrowed')->count(),
            'terlambat' => Borrowing::where('student_id', $student->id)
                                ->where('status', 'borrowed')
                                ->where('due_date', '<', now())
                                ->count(),
        ];

        return view('students.show', compact('student', 'borrowings', 'stats'));
    }

    public function update(Request $request, Student $student)
    {
        $validated = $request->validate([
            'nis'           => 'required|digits_between:1,7|unique:students,nis,' . $student->id,
            'name'          => 'required|string|max:255',
            'class'         => 'required|in:X,XI,XII',
            'major'         => 'nullable|string|max:255',
            'pob'           => 'required|string|max:100',
            'dob'           => 'required|date',
            'gender'        => 'required|in:Laki-laki,Perempuan',
            'address'       => 'required|string',
            'phone'         => 'required|numeric',
            'photo'         => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'status'        => 'required|in:active,inactive,graduated',
        ]);

        if ($request->hasFile('photo')) {
            // Hapus foto lama jika ada
            if ($student->photo) {
                Storage::disk('public')->delete($student->photo);
            }
            $validated['photo'] = $request->file('photo')->store('student_photos', 'public');
        }

        $student->update($validated);

        return redirect()->route('admin.students.index')->with('success', 'Data siswa berhasil diperbarui!');
    }

    public function destroy(Student $student)
    {
        if ($student->photo) {
            Storage::disk('public')->delete($student->photo);
        }
        
        $student->delete();
        return redirect()->route('admin.students.index')->with('success', 'Siswa berhasil dihapus!');
    }

    public function template()
    {
        return Excel::download(new StudentTemplateExport, 'template_data_siswa.xlsx');
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls|max:5120'
        ], [
            'file.required' => 'Pilih file Excel terlebih dahulu.',
            'file.mimes'    => 'Format file harus .xlsx atau .xls.',
            'file.max'      => 'Ukuran file tidak boleh lebih dari 5MB.'
        ]);

        try {
            Excel::import(new StudentsImport, $request->file('file'));
            return back()->with('success', 'Data Siswa berhasil diimport ke sistem!');
        } catch (\Maatwebsite\Excel\Validators\ValidationException $e) {
            $failures = $e->failures();
            $errorMsg = 'Kesalahan pada baris ke-' . $failures[0]->row() . ': ' . $failures[0]->errors()[0];
            return back()->with('error', 'Gagal Import! ' . $errorMsg);
        } catch (\Exception $e) {
            return back()->with('error', 'Gagal memproses file: ' . $e->getMessage());
        }
    }

    /**
     * View Siswa: Eksplorasi Buku
     */
    public function explore(Request $request)
    {
        $search = $request->input('search');
        $categoryId = $request->input('category');

        $books = Book::query()
            ->with('category')
            ->when($search, function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                ->orWhere('author', 'like', "%{$search}%");
            })
            ->when($categoryId, function($q) use ($categoryId) {
                $q->where('category_id', $categoryId);
            })
            ->latest()
            ->paginate(12);

        $categories = Category::all();
        $popularBooks = Book::popular(6);

        return view('siswa.explore', compact('books', 'categories', 'popularBooks'));
    }

    /**
     * Menampilkan daftar buku favorit siswa
     */
    public function favorites(Request $request)
    {
        $student = Auth::guard('student')->user();
 
        // 2. AMBIL SEMUA DATA KATEGORI UNTUK SIDEBAR FILTER
        $categories = Category::all();
 
        // --- START: Calculate Stats ---
        // Get all favorite book IDs for the current student to calculate stats
        $allFavoriteBookIds = Favorite::where('student_id', $student->id)->pluck('book_id');
 
        $allFavoriteBooks = Book::whereIn('id', $allFavoriteBookIds)
            ->withCount(['borrowingDetails as dipinjam_count' => function($q) {
                $q->whereHas('borrowing', function($query) {
                    $query->whereIn('status', ['borrowed', 'late'])
                          ->whereNull('return_date');
                });
            }])
            ->get();
 
        $tersediaCount = $allFavoriteBooks->filter(function($book) {
            return ($book->stock - $book->dipinjam_count) > 0;
        })->count();
 
        $habisCount = $allFavoriteBooks->count() - $tersediaCount;
 
        $stats = [
            'total' => $allFavoriteBooks->count(),
            'tersedia' => $tersediaCount,
            'habis' => $habisCount,
        ];
        // --- END: Calculate Stats ---
 
        // 3. QUERY DATA FAVORIT SISWA LENGKAP DENGAN FILTER (JIKA ADA)
        $query = Favorite::with(['book.category'])
            ->where('student_id', $student->id);
 
        // Filter berdasarkan pencarian kata kunci judul/penulis (Opsional jika mau dipakai)
        if ($request->has('search') && $request->search != '') {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('title', 'like', '%' . $request->search . '%')
                  ->orWhere('author', 'like', '%' . $request->search . '%');
            });
        }
 
        // Filter berdasarkan kategori dropdown di sidebar
        if ($request->has('category') && $request->category != '') {
            $query->whereHas('book', function($q) use ($request) {
                $q->where('category_id', $request->category);
            });
        }
 
        // Jalankan pagination
        $favorites = $query->orderBy('created_at', 'desc')->paginate(12);
 
        // 4. KIRIMKAN VARIABEL $categories DAN $stats KE VIEW
        return view('siswa.favorites', compact('favorites', 'categories', 'stats'));
    }

    /**
     * Menambahkan atau menghapus buku dari daftar favorit via AJAX
     */
    public function toggleFavorite(Request $request)
    {
        $request->validate(['book_id' => 'required|exists:books,id']);
        
        $studentId = auth()->guard('student')->id();
        $favorite = Favorite::where('student_id', $studentId)->where('book_id', $request->book_id)->first();

        if ($favorite) {
            $favorite->delete();
            return response()->json(['status' => 'removed', 'message' => 'Dihapus dari favorit']);
        } else {
            Favorite::create(['student_id' => $studentId, 'book_id' => $request->book_id]);
            return response()->json(['status' => 'added', 'message' => 'Ditambahkan ke favorit']);
        }
    }

    /**
     * Logika Peminjaman oleh Siswa
     */
    public function storeBorrow(Request $request)
    {
        $request->validate([
            'book_id' => 'required|exists:books,id',
        ]);

        $book = Book::findOrFail($request->book_id);
        $student = auth()->guard('student')->user();

        // 1. VALIDASI STATUS SISWA (Tambahkan ini)
        if ($student->status !== 'active') {
            $keterangan = ($student->status === 'graduated') ? 'LULUS' : 'NONAKTIF';
            
            return back()->with('error', "Akses Ditolak! Status Anda saat ini adalah $keterangan. Siswa yang sudah lulus atau tidak aktif tidak diperbolehkan meminjam buku baru.");
        }

        // 2. Cek stok buku
        if ($book->stock <= 0) {
            return back()->with('error', 'Maaf, stok buku ini sedang kosong.');
        }

        $book->load('category');
        $student->assertCanBorrowBooks(collect([$book]));
        $loanDays = Category::maxLoanDaysForBooks(collect([$book]));

        Borrowing::create([
            'transaction_code' => 'TRX-' . date('Ymd') . '-' . strtoupper(bin2hex(random_bytes(2))),
            'student_id' => $student->id,
            'book_id' => $book->id,
            'borrow_date' => now()->toDateString(),
            'due_date' => now()->addDays($loanDays)->toDateString(),
            'status' => 'pending',
        ]);

        $book->decrement('stock');

        return back()->with('success', 'Berhasil! Pengajuan peminjaman sedang diproses petugas.');
    }

    /**
     * Menampilkan daftar siswa khusus untuk halaman Kepala Perpustakaan (Head)
     * Dengan statistik dan grafik pendukung.
     */
    public function indexHead(Request $request)
    {
        // 1. Statistik Ringkasan (Data Asli)
        $total_siswa = Student::count();
        $siswa_aktif = Student::where('status', 'active')->count();
        $siswa_nonaktif = Student::where('status', 'inactive')->count();
        $total_kelas = Student::distinct('class')->count('class');
        $siswa_lulus = Student::where('status', 'graduated')->count();

        // 2. Query Daftar Siswa dengan Filter
        $query = Student::query();

        if ($request->filled('search')) {
            $query->where(function($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                  ->orWhere('nis', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->filled('class') && $request->class != 'Semua Kelas') {
            $query->where('class', $request->class);
        }

        $students = $query->latest()->paginate(10)->withQueryString();

        // 3. Data untuk Grafik (Pie Chart Kategori Kelas)
        $students_per_class = Student::select('class', DB::raw('count(*) as total'))
            ->groupBy('class')
            ->orderBy('total', 'desc')
            ->take(5)
            ->get();

        // 4. Data Jenis Kelamin (Dinamis untuk Chart)
        $laki_laki = Student::where('gender', 'Laki-laki')->count();
        $perempuan = Student::where('gender', 'Perempuan')->count();

        // 5. Ambil daftar kelas unik untuk dropdown filter
        $list_kelas = Student::distinct()->whereNotNull('class')->pluck('class');

        // Pastikan view path ini sesuai dengan struktur folder kamu
        return view('admin.head.students.index', compact(
            'total_siswa', 'siswa_aktif', 'siswa_nonaktif', 'total_kelas', 'siswa_lulus',
            'students', 'students_per_class', 'laki_laki', 'perempuan', 'list_kelas'
        ));
    }
}