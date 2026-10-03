<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Announcement extends Model
{
    protected $fillable = ['title', 'slug', 'content', 'image', 'type', 'user_id', 'published_at'];

    public function creator(): BelongsTo
    {
        // Hubungkan ke model User menggunakan foreign key 'user_id'
        return $this->belongsTo(User::class, 'user_id');
    }
}