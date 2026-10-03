<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Category extends Model
{
    use HasFactory;
    
    protected $guarded = [];
    protected $fillable = [
    'name', 
    'description', 
    'status',
    'loan_days',
    'quota_group',
];
    protected $casts = [
        'loan_days' => 'integer',
    ];
    
    // Relasi: Satu kategori punya banyak buku
    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function allowsBorrowing(): bool
    {
        return (int) $this->loan_days > 0;
    }

    public function isPaket(): bool
    {
        return $this->quotaGroup() === 'paket';
    }

    public function quotaGroup(): string
    {
        return ($this->quota_group ?? 'bebas') === 'paket' ? 'paket' : 'bebas';
    }

    public static function guessQuotaGroup(string $name): string
    {
        $n = strtolower($name);

        if (
            str_contains($n, 'paket')
            || str_contains($n, 'pelajaran')
            || str_contains($n, 'teks')
            || str_contains($n, 'wajib')
        ) {
            return 'paket';
        }

        return 'bebas';
    }

    /**
     * Durasi pinjam = hari terpanjang di antara buku yang dipilih.
     * Kategori dengan 0 hari (baca di tempat) tidak boleh dipinjam.
     */
    public static function maxLoanDaysForBooks($books): int
    {
        $maxDays = 0;
        $blocked = [];

        foreach ($books as $book) {
            $days = (int) ($book->category?->loan_days ?? 7);
            if ($days < 1) {
                $blocked[] = $book->title;
                continue;
            }
            $maxDays = max($maxDays, $days);
        }

        if ($blocked !== []) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'book_id' => 'Buku berikut hanya boleh dibaca di tempat (durasi 0 hari): ' . implode(', ', $blocked),
            ]);
        }

        if ($maxDays < 1) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'book_id' => 'Tidak ada durasi pinjam yang valid untuk buku yang dipilih.',
            ]);
        }

        return $maxDays;
    }
}