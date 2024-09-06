<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PhotoLocation extends Model
{
    use HasFactory;
    protected $fillable = [
        'photo_id',
        'gpx_id',
        'latitude',
        'longitude',
    ];
}
