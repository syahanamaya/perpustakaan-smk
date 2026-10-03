<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Borrowing;
use Illuminate\Support\Facades\Auth;

class BorrowingController extends Controller
{
    /**
     * Menyetujui pengajuan peminjaman dari siswa
     */
    public function approve($id)
    {
        // 1. Cari data peminjaman berdasarkan ID
        $borrowing = Borrowing::findOrFail($id);

        // 2. Pastikan hanya data yang statusnya 'pending' yang bisa disetujui
        if ($borrowing->status !== 'pending') {
            return back()->with('error', 'Transaksi ini sudah diproses sebelumnya.');
        }

        // 3. Update data peminjaman
        $borrowing->update([
            'status' => 'borrowed',           // Ubah status jadi dipinjam
            'user_id' => Auth::id(),        // Catat ID Admin yang sedang login
            'borrow_date' => now(),           // Tanggal pinjam resmi adalah hari ini
            'due_date' => now()->addDays(7),  // Otomatis jatuh tempo 7 hari ke depan
        ]);

        // 4. Kembali ke halaman dashboard dengan pesan sukses
        return back()->with('success', 'Peminjaman berhasil disetujui! Status kini: Sedang Dipinjam.');
    }
    
}