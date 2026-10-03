<?php

namespace App\Http\Controllers;

use App\Models\Borrowing;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use App\Models\FineSetting;

class StudentBorrowingController extends Controller
{
    public function index(Request $request)
    {
        $student = Auth::guard('student')->user();
        $statusFilter = $request->input('status', 'semua');

        // Ambil aturan denda yang aktif dari database untuk ditampilkan di info card
        $fineSetting = FineSetting::where('is_active', true)->first();

        // Query dasar dengan eager loading relasi 'fine'
        $query = Borrowing::with(['borrowingDetails.book.category', 'fine'])
            ->where('student_id', $student->id);

        // Terapkan filter status dari sidebar
        if ($statusFilter !== 'semua') {
            if ($statusFilter === 'dipinjam') {
                $query->where('status', 'borrowed')->where('due_date', '>=', now()->startOfDay());
            } elseif ($statusFilter === 'menunggu') {
                $query->where('status', 'pending');
            } elseif ($statusFilter === 'selesai') {
                $query->where('status', 'returned');
            } elseif ($statusFilter === 'terlambat') {
                $query->where('status', 'borrowed')->where('due_date', '<', now()->startOfDay());
            }
        }

        $borrowings = $query->orderBy('created_at', 'desc')->paginate(10);

        // Menghitung statistik untuk counter cards dan filter
        $allBorrowings = Borrowing::where('student_id', $student->id)->get();
        $stats = [
            'total' => $allBorrowings->count(),
            'dipinjam' => $allBorrowings->where('status', 'borrowed')->where('due_date', '>=', now()->startOfDay())->count(),
            'menunggu' => $allBorrowings->where('status', 'pending')->count(),
            'selesai' => $allBorrowings->where('status', 'returned')->count(),
            'terlambat' => $allBorrowings->where('status', 'borrowed')->where('due_date', '<', now()->startOfDay())->count(),
        ];

        // Mencari jatuh tempo terdekat untuk card
        $nearestDueDate = Borrowing::where('student_id', $student->id)
            ->where('status', 'borrowed')
            ->where('due_date', '>=', now()->startOfDay())
            ->min('due_date');

        $jatuhTempo = ['teks' => 'N/A', 'tanggal' => '-'];
        if ($nearestDueDate) {
            $dueDate = Carbon::parse($nearestDueDate);
            $diff = now()->startOfDay()->diffInDays($dueDate->startOfDay(), false);
            $jatuhTempo['teks'] = $diff == 0 ? 'Hari Ini' : $diff . ' Hari Lagi';
            $jatuhTempo['tanggal'] = $dueDate->translatedFormat('d M Y');
        }

        // Ganti nama variabel agar sesuai dengan yang ada di view
        $activeBorrowings = $borrowings;

        return view('siswa.borrowed-books', compact('activeBorrowings', 'stats', 'statusFilter', 'jatuhTempo', 'fineSetting'));
    }
}