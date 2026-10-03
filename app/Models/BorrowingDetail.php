<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class BorrowingDetail extends Model
{
    use HasFactory;

    protected $table = 'borrowing_details';

    protected $fillable = [
        'borrowing_id',
        'book_id',
        'qty',
        'status',
        'return_date',
        'condition',
        'damage_level',
        'damage_percent',
        'book_price_snapshot',
        'late_fine',
        'condition_fine',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class, 'book_id');
    }

    // WAJIB: Tambahkan ini agar sistem bisa membaca data peminjaman induknya
    public function borrowing(): BelongsTo
    {
        return $this->belongsTo(Borrowing::class, 'borrowing_id');
    }

    
}

