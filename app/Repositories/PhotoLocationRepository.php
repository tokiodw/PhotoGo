<?php

namespace App\Repositories;

use App\Models\PhotoLocation;

class PhotoLocationRepository {
    public function create($photoId, $gpxId, $latitude, $longitude) {
        return PhotoLocation::create([
            'photo_id' => $photoId,
            'gpx_id' => $gpxId,
            'latitude' => $latitude,
            'longitude' => $longitude,
        ]);
    }
}