<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoGroupStatus extends Model
{
    use HasFactory;

    protected $fillable = [
        'photo_group_id',
        'status',
        'status_type',
    ];
}
