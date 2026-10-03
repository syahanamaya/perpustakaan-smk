<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use App\Models\Fine;
use App\Models\FineSetting;
use Illuminate\Support\Facades\Auth;

class StudentFineController extends Controller
{
    public function index()
    {
        $user = Auth::guard('student')->user();

        // Ambil aturan denda yang aktif dari database
        $fineSetting = FineSetting::where('is_active', true)->first();

        // 1. Mengambil denda yang BELUM LUNAS (unpaid)
        // Kita cari dari tabel peminjaman milik siswa ini, yang punya relasi ke tabel fine dengan status unpaid
        $unpaidFines = Borrowing::with(['borrowingDetails.book', 'fine'])
            ->where('student_id', $user->id)
            ->whereHas('fine', function ($query) {
                $query->where('fine_status', 'unpaid'); // Sesuai kolom di gambar Anda
            })
            ->get();

        // 2. Mengambil riwayat denda yang SUDAH LUNAS (paid)
        $paidFines = Borrowing::with(['borrowingDetails.book', 'fine'])
            ->where('student_id', $user->id)
            ->whereHas('fine', function ($query) {
                $query->where('fine_status', 'paid'); // Sesuai kolom di gambar Anda
            })
            ->latest('updated_at') // Urutkan berdasarkan update terakhir
            ->paginate(5);

        // 3. Menghitung statistik denda
        // Karena kita memanggil Borrowing, kita ambil sum dari relasi finenya
        $totalBelumLunas = 0;
        foreach ($unpaidFines as $borrowing) {
            $totalBelumLunas += $borrowing->fine->total_fine; // Menggunakan kolom total_fine
        }

        $totalDibayar = Fine::whereHas('borrowing', function ($query) use ($user) {
            $query->where('student_id', $user->id);
        })->where('fine_status', 'paid')->sum('denda_dibayar'); // Menggunakan kolom denda_dibayar

        $stats = [
            'total_belum_lunas' => $totalBelumLunas,
            'jumlah_tanggungan' => $unpaidFines->count(),
            'total_dibayar' => $totalDibayar,
        ];

        return view('siswa.info-fine', compact('user', 'unpaidFines', 'paidFines', 'stats', 'fineSetting'));
    }
}
