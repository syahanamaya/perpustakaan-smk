<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FineSetting extends Model
{
    use HasFactory;

    protected $guarded = ['id'];

    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function damageCriteriaLabel(): string
    {
        return ($this->damage_light_percent ?? 25) . '% / '
            . ($this->damage_medium_percent ?? 50) . '% / '
            . ($this->damage_heavy_percent ?? 75) . '%';
    }
}