<?php

namespace App\Repositories;

use App\Models\PhotoGroupStatus;

class PhotoGroupStatusRepository {
    public function create($photoGroupId, $status_type, $status) {
        return PhotoGroupStatus::create([
            'photo_group_id' => $photoGroupId,
            'status_type' => $status_type,
            'status' => $status,
        ]);
    }
}