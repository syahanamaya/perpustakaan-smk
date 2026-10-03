<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class StockOpname extends Model
{
    protected $fillable = [
        'book_id',
        'user_id',
        'stok_sistem',
        'stok_dipinjam',
        'stok_master',
        'jumlah_hilang_sistem',
        'jumlah_rusak_ringan_sistem',
        'jumlah_rusak_sedang_sistem',
        'jumlah_rusak_berat_sistem',
        'stok_fisik',
        'jumlah_ditambah',
        'jumlah_hilang_ditemukan',
        'jumlah_rusak_ringan',
        'jumlah_rusak_sedang',
        'jumlah_rusak_berat',
        'selisih',
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

    public static function calculateSelisih(
        int $stokFisik,
        int $stokDipinjam,
        int $stokMaster,
        int $hilangDitemukan = 0,
        int $rusakRingan = 0,
        int $rusakSedang = 0,
        int $rusakBerat = 0
    ): int {
        $rusakTotal = $rusakRingan + $rusakSedang + $rusakBerat;

        return ($stokFisik + $stokDipinjam + $hilangDitemukan + $rusakTotal) - $stokMaster;
    }

    public function totalRusakDitemukan(): int
    {
        return (int) $this->jumlah_rusak_ringan
            + (int) $this->jumlah_rusak_sedang
            + (int) $this->jumlah_rusak_berat;
    }

    public function kondisiRingkasan(): string
    {
        $parts = [];

        if ((int) $this->jumlah_hilang_ditemukan > 0) {
            $parts[] = 'Hilang ' . $this->jumlah_hilang_ditemukan;
        }
        if ((int) $this->jumlah_rusak_ringan > 0) {
            $parts[] = 'Rusak Ringan ' . $this->jumlah_rusak_ringan;
        }
        if ((int) $this->jumlah_rusak_sedang > 0) {
            $parts[] = 'Rusak Sedang ' . $this->jumlah_rusak_sedang;
        }
        if ((int) $this->jumlah_rusak_berat > 0) {
            $parts[] = 'Rusak Berat ' . $this->jumlah_rusak_berat;
        }

        return count($parts) > 0 ? implode(', ', $parts) : 'Tidak ada temuan rusak/hilang';
    }

    public function selisihLabel(): string
    {
        if ($this->selisih === 0) {
            return 'Sesuai';
        }

        return $this->selisih > 0 ? 'Lebih ' . $this->selisih : 'Kurang ' . abs($this->selisih);
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

    public function statusColor(): string
    {
        return match ($this->status) {
            'menunggu_validasi' => 'amber',
            'ditolak' => 'red',
            'disetujui' => 'green',
            default => 'gray',
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

        $book->stock = $this->stok_fisik;

        if ((int) $this->jumlah_hilang_ditemukan > 0) {
            $book->increment('jumlah_hilang', (int) $this->jumlah_hilang_ditemukan);
        }

        $rusakTotal = $this->totalRusakDitemukan();
        if ($rusakTotal > 0) {
            $book->increment('jumlah_rusak', $rusakTotal);
            if ((int) $this->jumlah_rusak_ringan > 0) {
                $book->increment('jumlah_rusak_ringan', (int) $this->jumlah_rusak_ringan);
            }
            if ((int) $this->jumlah_rusak_sedang > 0) {
                $book->increment('jumlah_rusak_sedang', (int) $this->jumlah_rusak_sedang);
            }
            if ((int) $this->jumlah_rusak_berat > 0) {
                $book->increment('jumlah_rusak_berat', (int) $this->jumlah_rusak_berat);
            }
        }

        $book->last_stock_take_at = now();
        $book->verified_by = $this->validated_by;
        $book->save();
        $book->syncOpnameStatus();
    }
}
