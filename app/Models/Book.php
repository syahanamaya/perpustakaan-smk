<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Book extends Model
{
    use HasFactory;

    protected $fillable = [
        'book_code',
        'title',
        'category_id',
        'author',
        'publisher',
        'isbn',
        'publication_year',
        'stock',
        'jumlah_rusak',
        'jumlah_rusak_ringan',
        'jumlah_rusak_sedang',
        'jumlah_rusak_berat',
        'jumlah_hilang',
        'stock_status',
        'last_stock_take_at',
        'verified_by',
        'rak_id',
        'cover_image',
        'description',
        'price'
    ];

    protected $casts = [
        'last_stock_take_at' => 'datetime',
    ];

    public function category()
    {
        return $this->belongsTo(Category::class, 'category_id', 'id');
    }

    public function rak()
    {
        return $this->belongsTo(Rak::class);
    }

    public function borrowingDetails(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class, 'book_id');
    }

    public function details(): HasMany
    {
        return $this->hasMany(BorrowingDetail::class, 'book_id');
    }

    public function favoritedBy()
    {
        return $this->belongsToMany(Student::class, 'favorites', 'book_id', 'student_id');
    }

    public function verifiedBy()
    {
        return $this->belongsTo(User::class, 'verified_by');
    }

    public function stockOpnames(): HasMany
    {
        return $this->hasMany(StockOpname::class);
    }

    public function stockAdditions(): HasMany
    {
        return $this->hasMany(StockAddition::class);
    }

    public function borrowedCount(): int
    {
        return $this->borrowingDetails()
            ->whereHas('borrowing', function ($q) {
                $q->whereIn('status', ['borrowed', 'late'])
                    ->whereNull('return_date');
            })
            ->count();
    }

    /**
     * Total eksemplar judul: di rak + dipinjam + sudah dicatat hilang.
     */
    public function masterCopyCount(?int $dipinjam = null): int
    {
        $loaned = $dipinjam ?? $this->borrowedCount();

        return (int) $this->stock + $loaned + (int) ($this->jumlah_hilang ?? 0);
    }

    public function missingStatusLabel(?int $dipinjam = null): string
    {
        $hilang = (int) ($this->jumlah_hilang ?? 0);
        $master = $this->masterCopyCount($dipinjam);
        if ($master < 1) {
            $master = max(1, $hilang);
        }
        if ($hilang < 1) {
            return 'Hilang';
        }

        return 'Hilang ' . $hilang . ' dari ' . $master;
    }

    public function damagedStatusLabel(?int $dipinjam = null): string
    {
        $ringan = (int) ($this->jumlah_rusak_ringan ?? 0);
        $sedang = (int) ($this->jumlah_rusak_sedang ?? 0);
        $berat = (int) ($this->jumlah_rusak_berat ?? 0);
        $total = $ringan + $sedang + $berat;
        if ($total < 1) {
            $total = (int) ($this->jumlah_rusak ?? 0);
        }

        $master = $this->masterCopyCount($dipinjam);
        if ($master < 1) {
            $master = max(1, $total);
        }
        if ($total < 1) {
            return 'Rusak';
        }

        $parts = [];
        if ($ringan > 0) {
            $parts[] = 'Ringan ' . $ringan;
        }
        if ($sedang > 0) {
            $parts[] = 'Sedang ' . $sedang;
        }
        if ($berat > 0) {
            $parts[] = 'Berat ' . $berat;
        }

        if (count($parts) === 1) {
            return 'Rusak ' . $parts[0] . ' dari ' . $master;
        }
        if (count($parts) > 1) {
            return 'Rusak ' . implode(', ', $parts) . ' dari ' . $master;
        }

        return 'Rusak ' . $total . ' dari ' . $master;
    }

    public function derivedStockStatus(): string
    {
        if ((int) ($this->jumlah_hilang ?? 0) > 0) {
            return 'missing';
        }

        $rusakLevel = (int) ($this->jumlah_rusak_ringan ?? 0)
            + (int) ($this->jumlah_rusak_sedang ?? 0)
            + (int) ($this->jumlah_rusak_berat ?? 0);
        $rusak = $rusakLevel > 0 ? $rusakLevel : (int) ($this->jumlah_rusak ?? 0);

        if ($rusak > 0) {
            return 'damaged';
        }

        return 'verified';
    }

    public function syncOpnameStatus(): void
    {
        $this->stock_status = $this->derivedStockStatus();
        $this->save();
    }

    public function opnameStatusLabel(?int $dipinjam = null): string
    {
        return match ($this->derivedStockStatus()) {
            'missing' => $this->missingStatusLabel($dipinjam),
            'damaged' => $this->damagedStatusLabel($dipinjam),
            default => 'Terverifikasi',
        };
    }

    public static function popular(int $limit = 8)
    {
        return static::query()
            ->with('category')
            ->withCount('borrowingDetails as borrow_count')
            ->orderByDesc('borrow_count')
            ->orderBy('title')
            ->limit($limit)
            ->get();
    }
}
