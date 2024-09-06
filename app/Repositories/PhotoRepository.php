<?php

namespace App\Repositories;

use App\Models\Photo;

class PhotoRepository {
    public function create($userId, $photoGroupId, $fileName, $storageDir, $takenAt) {
        return Photo::create([
            'user_id' => $userId,
            'photo_group_id' => $photoGroupId,
            'original_name' => $fileName,
            'storage_dir' => $storageDir,
            'taken_at' => $takenAt,
        ]);
    }

    public function findByPhotoGroupId($photoGroupId) {
        return Photo::where('photo_group_id', $photoGroupId)->get();
    }
}