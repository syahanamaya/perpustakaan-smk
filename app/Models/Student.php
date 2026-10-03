<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Student extends Authenticatable
{
    use HasFactory;

    // Tambahkan field baru sesuai struktur database
    protected $fillable = [
        'nis',
        'name',
        'pob',          // Tempat Lahir
        'dob',          // Tanggal Lahir
        'gender',
        'address',
        'phone',
        'photo',
        'class',
        'major',
        'status',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'dob' => 'date', // Cast ke format date
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
        'password' => 'hashed',
    ];

    public function borrowings(): HasMany
    {
        return $this->hasMany(Borrowing::class);
    }

    public function loanQuota(string $group = 'all'): int
    {
        $rule = \App\Support\ClassLoanQuota::forClass($this->class);

        if ($group === 'paket') {
            return (int) ($rule['max_paket'] ?? 4);
        }

        if ($group === 'bebas') {
            return (int) ($rule['max_bebas'] ?? 2);
        }

        return (int) ($rule['max_paket'] ?? 4) + (int) ($rule['max_bebas'] ?? 2);
    }

    public function activeBorrowedCopies(string $group = 'all'): int
    {
        $details = BorrowingDetail::query()
            ->with('book.category')
            ->where('status', 'borrowed')
            ->whereHas('borrowing', function ($q) {
                $q->where('student_id', $this->id)
                    ->whereIn('status', ['borrowed', 'pending', 'late']);
            })
            ->get();

        $count = $details->filter(function ($detail) use ($group) {
            if ($group === 'all') {
                return true;
            }

            return ($detail->book?->category?->quotaGroup() ?? 'bebas') === $group;
        })->count();

        $pendingWithoutDetails = Borrowing::query()
            ->with('book.category')
            ->where('student_id', $this->id)
            ->where('status', 'pending')
            ->whereDoesntHave('details')
            ->get()
            ->filter(function ($borrowing) use ($group) {
                if ($group === 'all') {
                    return true;
                }

                return ($borrowing->book?->category?->quotaGroup() ?? 'bebas') === $group;
            })
            ->count();

        return $count + $pendingWithoutDetails;
    }

    public function remainingLoanSlots(string $group = 'all'): int
    {
        return max(0, $this->loanQuota($group) - $this->activeBorrowedCopies($group));
    }

    public function assertCanBorrowBooks($books): void
    {
        $baruPaket = 0;
        $baruBebas = 0;

        foreach ($books as $book) {
            if (($book->category?->quotaGroup() ?? 'bebas') === 'paket') {
                $baruPaket++;
            } else {
                $baruBebas++;
            }
        }

        $sisaPaket = $this->remainingLoanSlots('paket');
        $sisaBebas = $this->remainingLoanSlots('bebas');

        if ($baruPaket > $sisaPaket) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'book_id' => "Kuota buku paket/pelajaran kelas {$this->class} tersisa {$sisaPaket} (maksimal {$this->loanQuota('paket')}, sudah pinjam {$this->activeBorrowedCopies('paket')}).",
            ]);
        }

        if ($baruBebas > $sisaBebas) {
            throw \Illuminate\Validation\ValidationException::withMessages([
                'book_id' => "Kuota buku bebas (novel/komik/umum) kelas {$this->class} tersisa {$sisaBebas} (maksimal {$this->loanQuota('bebas')}, sudah pinjam {$this->activeBorrowedCopies('bebas')}).",
            ]);
        }
    }
}