<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\Student;
use App\Models\Borrowing;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
   public function index()
    {
        $now = Carbon::now();

        // 1. KOTAK RINGKASAN ATAS
        $total_books = Book::count(); 
        $active_borrowings = Borrowing::where('status', 'borrowed')->count();
        
        $total_borrowings_month = Borrowing::whereMonth('borrow_date', $now->month)
            ->whereYear('borrow_date', $now->year)->count();
            
        $total_returns_month = Borrowing::where('status', 'returned')
            ->whereMonth('updated_at', $now->month)
            ->whereYear('updated_at', $now->year)->count();

        // TRANSAKSI TERLAMBAT
        $late_transactions = Borrowing::where('status', 'borrowed')
            ->where('due_date', '<', $now)->get();
        $late_count = $late_transactions->count();
        $late_students_count = $late_transactions->pluck('student_id')->unique()->count();

        $completed_borrowings = Borrowing::where('status', 'returned')->count();

        // 2. DATA GRAFIK 6 BULAN TERAKHIR
        $months = [];
        $borrow_data = [];
        $return_data = [];
        
        for ($i = 5; $i >= 0; $i--) {
            $month = $now->copy()->subMonths($i);
            $months[] = $month->translatedFormat('M Y');
            
            $borrow_data[] = Borrowing::whereMonth('borrow_date', $month->month)
                ->whereYear('borrow_date', $month->year)->count();
                
            $return_data[] = Borrowing::where('status', 'returned')
                ->whereMonth('updated_at', $month->month)
                ->whereYear('updated_at', $month->year)->count();
        }

        // 3. SISWA PALING AKTIF
        $popular_students = Student::withCount('borrowings')
            ->orderBy('borrowings_count', 'desc')
            ->take(4)
            ->get();

        // 4. BUKU PALING POPULER
        $popular_books = DB::table('borrowing_details')
            ->join('books', 'borrowing_details.book_id', '=', 'books.id')
            ->select('books.title','books.cover_image', 'books.author', DB::raw('count(borrowing_details.book_id) as details_count'), DB::raw('count(borrowing_details.borrowing_id) as total_borrowed'))
            ->groupBy('borrowing_details.book_id', 'books.title', 'books.cover_image', 'books.author')
            ->orderBy('details_count', 'desc')
            ->take(5)
            ->get();

        $book_labels = $popular_books->pluck('title')->toArray();
        $book_data = $popular_books->pluck('details_count')->toArray();

        // 5. AKTIVITAS TERBARU
        $recent_activities = Borrowing::with('student')
            ->orderBy('updated_at', 'desc')
            ->take(4)
            ->get();

        // 6. MENGHITUNG DENDA (DIUBAH KE TABEL BORROWING AGAR SINKRON)
        
        // ==============================================================
        // MENGHITUNG DENDA (DIARAHKAN KE TABEL fines)
        // ==============================================================
        
        // Total uang denda yang sudah dibayar bulan ini
        $denda_dibayar = DB::table('fines')
            ->where('fine_status', 'paid') 
            ->whereMonth('updated_at', $now->month)
            ->whereYear('updated_at', $now->year)
            ->sum('denda_dibayar');

        // Denda yang belum dibayar
        $denda_belum = DB::table('fines')
            ->where('fine_status', 'unpaid')
            ->where('total_fine', '!=', 0)
            ->get()
            ->sum(function($item) {
                return abs($item->total_fine ?? 0) - abs($item->denda_dibayar ?? 0);
            });

        // Total seluruh potensi denda
        $total_fine = DB::table('fines')
            ->where('total_fine', '!=', 0)
            ->sum('total_fine');

        return view('admin.dashboard', compact(
            'total_books', 'active_borrowings', 'total_borrowings_month', 'total_returns_month',
            'late_count', 'late_students_count', 'completed_borrowings',
            'months', 'borrow_data', 'return_data', 
            'popular_students','popular_books', 'book_labels', 'book_data', 'recent_activities',
            'total_fine', 'denda_dibayar', 'denda_belum'
        ));
    }
}