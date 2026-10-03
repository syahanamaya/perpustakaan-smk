<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Borrowing extends Model
{
    use HasFactory;

    protected $fillable = [
        'transaction_code', 
        'student_id',
        'user_id',
        'book_id', 
        'borrow_date',
        'due_date',
        'status',        
        'return_date'    
    ];

    protected $casts = [
        'borrow_date' => 'datetime',
        'due_date' => 'datetime',
        'return_date' => 'datetime',
    ];

    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * PERBAIKAN: Gunakan BelongsTo karena book_id ada di tabel borrowings
     */
    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }
    
    public function details()
    {
        return $this->hasMany(BorrowingDetail::class, 'borrowing_id');
    }

    public function fine()
    {
        return $this->hasOne(Fine::class, 'borrowing_id', 'id');
    }

    public function getBookCountAttribute()
    {
        return $this->details()->count();
    }

    /**
     * Accessor: Jika view memanggil $borrowing->total_fine, 
     * otomatis diarahkan mengambil dari tabel fines (jika ada).
     */
    public function getTotalFineAttribute()
    {
        return $this->fine ? abs($this->fine->total_fine) : 0;
    }

    public function getDendaAttribute()
    {
        return $this->fine ? abs($this->fine->total_fine) : 0;
    }

    public function getTotalDendaAttribute()
    {
        return $this->fine ? abs($this->fine->total_fine) : 0;
    }

    public function borrowingDetails()
    {
        return $this->hasMany(BorrowingDetail::class, 'borrowing_id', 'id');
    }
}