<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Photo extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'photo_group_id',
        'original_name',
        'storage_dir',
        'taken_at',
    ];
    
}
