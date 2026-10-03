<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockAddition extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'stok_sebelum',
        'jumlah_ditambah',
        'stok_sesudah',
        'foto_path',
        'catatan',
        'status',
        'rejection_reason',
        'submitted_at',
        'resubmitted_at',
        'validated_by',
        'validated_at',
        'revision_count',
    ];

    protected $casts = [
        'submitted_at' => 'datetime',
        'resubmitted_at' => 'datetime',
        'validated_at' => 'datetime',
    ];

    public function book(): BelongsTo
    {
        return $this->belongsTo(Book::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function validator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'validated_by');
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'menunggu_validasi' => 'Menunggu Validasi',
            'ditolak' => 'Ditolak',
            'disetujui' => 'Disetujui',
            default => ucfirst(str_replace('_', ' ', $this->status)),
        };
    }

    public function canEdit(): bool
    {
        return $this->status === 'ditolak';
    }

    public function applyToBook(): void
    {
        $book = $this->book;
        if (! $book) {
            return;
        }

        $book->stock = (int) $this->stok_sebelum + (int) $this->jumlah_ditambah;
        $book->last_stock_take_at = now();
        $book->verified_by = $this->validated_by;
        $book->save();
        $book->syncOpnameStatus();
    }
}
