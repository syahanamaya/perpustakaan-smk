<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\Book;
use App\Models\Announcement;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function index()
    {
        // Mengambil data siswa yang sedang login melalui guard student
        $student = auth()->guard('student')->user();

        // 1. Ambil Pinjaman Aktif (Sedang Dipinjam atau Menunggu)
        $activeBorrowings = Borrowing::with('details.book')
            ->where('student_id', $student->id)
            ->whereIn('status', ['borrowed', 'pending'])
            ->get();

        // 2. Hitung Total Denda (Hanya yang belum lunas)
        $totalDenda = Fine::whereHas('borrowing', function($q) use ($student) {
            $q->where('student_id', $student->id);
        })->where('fine_status', 'unpaid')
          ->sum('total_fine');

        // 3. Logika Peringatan Status
        $statusWarning = null;
        if ($student->status === 'graduated') {
            $statusWarning = "Status Anda: LULUS. Anda hanya diperbolehkan mengembalikan buku.";
        } elseif ($student->status === 'inactive') {
            $statusWarning = "Status Akun: NONAKTIF. Silahkan hubungi petugas perpustakaan.";
        }

        $recommendedBooks = Book::popular(5);

        // 5. Pengumuman Terbaru
        $latestAnnouncements = Announcement::latest()->limit(3)->get();

        // 6. Riwayat Peminjaman Terakhir
        $borrowingHistory = Borrowing::with('details.book')
            ->where('student_id', $student->id)
            ->latest('borrow_date')
            ->take(5)
            ->get();

        return view('siswa.dashboard', compact('student', 'activeBorrowings', 'totalDenda', 'statusWarning', 'recommendedBooks', 'latestAnnouncements', 'borrowingHistory'));
    }

    /**
     * FUNGSI BARU: MENAMPILKAN RIWAYAT TRANSAKSI SELESAI
     */
    public function history(Request $request)
    {
        $student = Auth::guard('student')->user();

        // Ambil data transaksi peminjaman yang statusnya sudah dikembalikan ('returned')
        // Sesuaikan 'details.book' dengan nama relasi di model Borrowing kamu
        $query = Borrowing::with(['details.book', 'fine'])
            ->where('student_id', $student->id)
            ->where('status', 'returned');
            
        // 1. Filter Waktu
        if ($request->filled('time')) {
            if ($request->time === 'month') {
                $query->whereMonth('return_date', now()->month)
                      ->whereYear('return_date', now()->year);
            } elseif ($request->time === 'year') {
                $query->whereYear('return_date', now()->year);
            }
        }

        // 2. Filter Status
        if ($request->filled('status')) {
            if ($request->status === 'returned') {
                // Dikembalikan tepat waktu (tanpa denda / denda = 0)
                $query->where(function($q) {
                    $q->doesntHave('fine')
                      ->orWhereHas('fine', function($sq) {
                          $sq->where('total_fine', 0);
                      });
                });
            } elseif ($request->status === 'fine_paid') {
                // Ada denda dan sudah lunas
                $query->whereHas('fine', function($q) {
                    $q->where('total_fine', '>', 0)->where('fine_status', 'paid');
                });
            }
        }

        // Diurutkan dari yang paling baru dikembalikan
        $historyBorrowings = $query->orderBy('return_date', 'desc')->paginate(10)->withQueryString();

        // Hitung total keseluruhan riwayat tanpa filter untuk counter badge "Semua Waktu"
        $totalAll = Borrowing::where('student_id', $student->id)->where('status', 'returned')->count();

        // Mengarah ke file view riwayat peminjaman di folder resources/views/siswa/history.blade.php
        return view('siswa.history', compact('historyBorrowings', 'totalAll'));
    }

    public function editProfile()
    {
        $student = Auth::guard('student')->user();
        return view('siswa.edit-profile', compact('student'));
    }

    public function updateProfile(Request $request)
    {
        $student = Auth::guard('student')->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'photo' => 'nullable|image|mimes:jpg,jpeg,png|max:2048',
            'password' => 'nullable|min:6',
        ]);

        $student->name = $request->name;

        if ($request->hasFile('photo')) {
            if ($student->photo && Storage::disk('public')->exists($student->photo)) {
                Storage::disk('public')->delete($student->photo);
            }
            $path = $request->file('photo')->store('student_photos', 'public');
            $student->photo = $path;
        }

        if ($request->filled('password')) {
            $student->password = Hash::make($request->password);
        }

        $student->save();

        return redirect()->route('student.dashboard')->with('success', 'Profil berhasil diperbarui!');
    }
    public function announcements(Request $request)
    {
        $query = Announcement::query();

        // 1. Filter Cari Pengumuman (Bilah Kanan Atas)
        if ($request->filled('search')) {
            $query->where('title', 'like', '%' . $request->search . '%')
                ->orWhere('content', 'like', '%' . $request->search . '%');
        }

        // 2. Filter Tipe Kategori Pengumuman (Sidebar Kanan)
        if ($request->filled('type') && in_array($request->type, ['penting', 'info', 'kegiatan', 'pengingat'])) {
            $query->where('type', $request->type);
        }

        // Ambil data pengumuman utama dengan pagination (5 item per halaman sesuai gambar)
        $announcements = $query->latest()->paginate(5);

        // 3. Ambil 3 Pengumuman Terbaru untuk komponen Sidebar Kanan Bawah
        $latestAnnouncements = Announcement::latest()->limit(3)->get();

        // 4. Hitung Counter Jumlah per Kategori secara dinamis untuk Sidebar
        $counts = [
            'all'       => Announcement::count(),
            'penting'   => Announcement::where('type', 'penting')->count(),
            'info'      => Announcement::where('type', 'info')->count(),
            'kegiatan'  => Announcement::where('type', 'kegiatan')->count(),
            'pengingat' => Announcement::where('type', 'pengingat')->count(),
        ];

        return view('siswa.announcements', compact('announcements', 'latestAnnouncements', 'counts'));
    }

    /**
     * FUNGSI BARU: HALAMAN DETAIL BACA SELENGKAPNYA
     */
    public function showAnnouncement($slug)
    {
        $announcement = Announcement::where('slug', $slug)->firstOrFail();
        return view('siswa.announcement-detail', compact('announcement'));
    }
}