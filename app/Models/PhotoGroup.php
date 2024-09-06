<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoGroup extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'group_type',
        'file_name',
        'group_name',
        'photo_count',
        'non_photo_count',
    ];
}
