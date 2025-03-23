<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Factories\HasFactory;

class Snippet extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'code',
        'language',
        'is_favourite',
        'is_deleted'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function tags()
    {
        return $this->belongsToMany(Tag::class, 'snippet_tag');
    }
}
