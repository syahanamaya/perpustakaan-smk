<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Fine extends Model
{
    use HasFactory;

    protected $fillable = [
        'borrowing_id',
        'total_fine',
        'denda_dibayar',
        'fine_status',
        'keterangan'
    ];
    protected $guarded = ['id'];
    public function student()
    {
        return $this->belongsTo(Student::class, 'student_id');
    }
    public function borrowing()
    {
        return $this->belongsTo(Borrowing::class, 'borrowing_id');
    }
}