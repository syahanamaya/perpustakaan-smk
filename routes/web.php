<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\BookController;
use App\Http\Controllers\StudentController;
use App\Http\Controllers\TransactionController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentBorrowingController;
use App\Http\Controllers\StudentFineController;
use App\Http\Controllers\Admin\DashboardController as AdminDashboard; 
use App\Http\Controllers\Admin\HeadDashboardController;
use App\Http\Controllers\Admin\BorrowingController;
use App\Http\Controllers\RakController;
use App\Http\Controllers\Admin\Head\ReportController;
use App\Http\Controllers\Admin\Head\TransactionHistoryController;
use App\Http\Controllers\Admin\Head\FineRecapController;
use App\Http\Controllers\Admin\Head\FineSettingController;
use App\Http\Controllers\Admin\Head\AnnouncementController; 
use App\Http\Controllers\Admin\Head\ClassLoanRuleController; 
use App\Http\Controllers\Admin\Head\StockValidationController;
use App\Http\Controllers\StockOpnameController;
use App\Http\Controllers\StockAdditionController;

// --- 1. GUEST ROUTES ---
Route::middleware('guest')->group(function () {
    Route::get('/login', [AuthController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [AuthController::class, 'login'])->name('login.process');
});

Route::post('/logout', [AuthController::class, 'logout'])->name('logout');

// --- 2. ROOT REDIRECT ---
Route::get('/', function () {
    if (Auth::guard('web')->check()) {
        return Auth::user()->role === 'head' 
            ? redirect()->route('head.dashboard') 
            : redirect()->route('admin.dashboard');
    }
    if (Auth::guard('student')->check()) {
        // Samakan dengan nama route di bawah
        return redirect()->route('student.dashboard'); 
    }
    return redirect()->route('login');
});

// --- 3. KEPALA PERPUSTAKAAN ---
Route::middleware(['auth:web', 'role:head'])->prefix('head')->name('head.')->group(function () {
    Route::get('/dashboard', [HeadDashboardController::class, 'index'])->name('dashboard');
    Route::get('/reports', [ReportController::class, 'index'])->name('reports.index');
    Route::get('/reports/export', [ReportController::class, 'export'])->name('reports.export');
    Route::get('/books', [BookController::class, 'collection'])->name('books.collection');
    Route::get('/fines/recap', [FineRecapController::class, 'index'])->name('fines.recap');
    Route::get('/fines/recap/export', [FineRecapController::class, 'export'])->name('fines.recap.export');
    Route::get('/students', [StudentController::class, 'indexHead'])->name('students.index');
    Route::get('/transactions', [TransactionHistoryController::class, 'index'])->name('transactions.index');
    Route::get('/transactions/export', [TransactionHistoryController::class, 'export'])->name('transactions.export');
    Route::get('/settings/fines', [FineSettingController::class, 'index'])->name('fines.index');
    Route::post('/settings/fines', [FineSettingController::class, 'store'])->name('fines.store');
    Route::get('/settings/loan-rules', [ClassLoanRuleController::class, 'index'])->name('loan-rules.index');
    Route::put('/settings/loan-rules', [ClassLoanRuleController::class, 'update'])->name('loan-rules.update');
    Route::put('/settings/loan-rules/durations', [ClassLoanRuleController::class, 'updateDurations'])->name('loan-rules.durations');
    Route::resource('users', UserController::class)->except(['create', 'show', 'edit']);
    Route::post('/announcements/{announcement}/status', [AnnouncementController::class, 'updateStatus'])->name('announcements.updateStatus');
    Route::resource('/announcements', AnnouncementController::class)->except(['show', 'create', 'edit']);

    Route::get('/stock-validation', [StockValidationController::class, 'index'])->name('stock-validation.index');
    Route::get('/stock-validation/history', [StockValidationController::class, 'history'])->name('stock-validation.history');
    Route::get('/stock-validation/export', [StockValidationController::class, 'export'])->name('stock-validation.export');
    Route::get('/stock-validation/opname/{stockOpname}', [StockValidationController::class, 'showOpname'])->name('stock-validation.show-opname');
    Route::patch('/stock-validation/opname/{stockOpname}/approve', [StockValidationController::class, 'approveOpname'])->name('stock-validation.approve-opname');
    Route::patch('/stock-validation/opname/{stockOpname}/reject', [StockValidationController::class, 'rejectOpname'])->name('stock-validation.reject-opname');
    Route::get('/stock-validation/addition/{stockAddition}', [StockValidationController::class, 'showAddition'])->name('stock-validation.show-addition');
    Route::patch('/stock-validation/addition/{stockAddition}/approve', [StockValidationController::class, 'approveAddition'])->name('stock-validation.approve-addition');
    Route::patch('/stock-validation/addition/{stockAddition}/reject', [StockValidationController::class, 'rejectAddition'])->name('stock-validation.reject-addition');
});

// --- 4. ADMIN / PETUGAS (Penambahan Prefix 'admin') ---
Route::middleware(['auth:web', 'role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboard::class, 'index'])->name('dashboard');

    // Master Data (Sekarang URL-nya jadi /admin/books, dst)
    Route::post('/books/import', [BookController::class, 'import'])->name('books.import');
    Route::get('/books/template', [BookController::class, 'template'])->name('books.template');
    Route::get('/books/{book}/borrow-history', [BookController::class, 'borrowHistory'])->name('books.borrow-history');
    Route::resource('books', BookController::class);

    Route::post('/students/import', [StudentController::class, 'import'])->name('students.import');
    Route::get('/students/template', [StudentController::class, 'template'])->name('students.template');
    Route::resource('students', StudentController::class);
    Route::resource('categories', CategoryController::class)->except(['create', 'edit', 'show']);
    Route::resource('raks', RakController::class);
    
    // Sirkulasi
    Route::get('/transactions/history', [TransactionController::class, 'history'])->name('transactions.history');
    Route::get('/transactions/history/export', [TransactionController::class, 'exportHistory'])->name('transactions.history.export');
    Route::get('/transactions/check-student', [TransactionController::class, 'checkStudent'])->name('transactions.checkStudent');
    Route::get('/transactions/search-book', [TransactionController::class, 'searchBook'])->name('transactions.searchBook');
    
    Route::get('/fines', [TransactionController::class, 'fineIndex'])->name('fines.index');
    Route::patch('/fines/{borrowing}/pay', [TransactionController::class, 'payFine'])->name('fines.pay');
    
    Route::get('/returns', [TransactionController::class, 'returnIndex'])->name('returns.index');
    Route::get('/transactions/{borrowing:transaction_code}/return', [TransactionController::class, 'returnPage'])->name('transactions.return');
    Route::post('/returns/process', [TransactionController::class, 'processReturn'])->name('returns.process');
    
    Route::resource('transactions', TransactionController::class)->parameters([
        'transactions' => 'borrowing'
    ]);
    Route::get('/transactions/{borrowing}/print', [TransactionController::class, 'print'])->name('transactions.print');
    Route::get('/transactions/{borrowing}/pdf', [TransactionController::class, 'pdf'])->name('transactions.pdf');
    Route::patch('/borrowings/{id}/approve', [BorrowingController::class, 'approve'])->name('transactions.approve');

    Route::get('/stock-opname/export', [StockOpnameController::class, 'export'])->name('stock-opname.export');
    Route::get('/stock-opname/book-snapshot', [StockOpnameController::class, 'bookSnapshot'])->name('stock-opname.book-snapshot');
    Route::resource('stock-opname', StockOpnameController::class)->except(['destroy']);

    Route::get('/stock-addition/export', [StockAdditionController::class, 'export'])->name('stock-addition.export');
    Route::resource('stock-addition', StockAdditionController::class)->except(['destroy']);
});

// --- 5. SISWA (Guard: student) ---
Route::middleware(['auth:student'])->group(function () {
    // Gunakan Controller yang kita buat tadi, jangan pakai closure function lagi di sini
    Route::get('/dashboard', [DashboardController::class, 'index'])->name('student.dashboard');
    Route::get('/siswa/explore', [StudentController::class, 'explore'])->name('student.explore');
    Route::get('/siswa/borrowed-books', [StudentBorrowingController::class, 'index'])->name('student.borrowed-books');
    Route::get('/siswa/info-fine', [StudentFineController::class, 'index'])->name('student.info-fine');
    Route::get('/siswa/history', [DashboardController::class, 'history'])->name('student.history');
    Route::get('/profile/edit', [DashboardController::class, 'editProfile'])->name('student.profile.edit');
    Route::put('/profile/update', [DashboardController::class, 'updateProfile'])->name('student.profile.update');
    Route::get('/favorit-saya', [StudentController::class, 'favorites'])->name('student.favorites');
    Route::post('/favorit/toggle', [StudentController::class, 'toggleFavorite'])->name('student.favorite.toggle');
    Route::get('/announcements', [DashboardController::class, 'announcements'])->name('siswa.announcements');
    Route::get('/announcements/{slug}', [DashboardController::class, 'showAnnouncement'])->name('siswa.announcements.show');
});