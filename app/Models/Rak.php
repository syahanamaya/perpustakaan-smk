<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Rak extends Model
{
    protected $fillable = [
        'kode_rak',
        'nama_rak',
        'lokasi',
        'kapasitas'
    ];

    public function books(): HasMany
    {
        return $this->hasMany(Book::class, 'rak_id');
    }

    // Atribut bantuan untuk menghitung jumlah buku yang ada di rak ini
    public function getTerisiAttribute()
    {
        return $this->books()->sum('stock');
    }

    // Atribut bantuan untuk menghitung persentase
    public function getPersentaseAttribute()
    {
        if ($this->kapasitas <= 0) return 0;
        $persen = ($this->terisi / $this->kapasitas) * 100;
        return round($persen > 100 ? 100 : $persen);
    }
}