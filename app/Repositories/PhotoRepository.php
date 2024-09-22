<?php

namespace App\Repositories;

use App\Models\Photo;

class PhotoRepository
{
    public function create($userId, $photoGroupId, $fileName, $storageDir, $thumbnailDir, $takenAt)
    {
        return Photo::create([
            'user_id' => $userId,
            'photo_group_id' => $photoGroupId,
            'original_name' => $fileName,
            'storage_dir' => $storageDir,
            'thumbnail_dir' => $thumbnailDir,
            'taken_at' => $takenAt,
        ]);
    }

    public function findByPhotoGroupId($photoGroupId, $userId = null) {
        $query = Photo::where('photo_group_id', $photoGroupId);

        if ($userId) {
            return $query->where('user_id', $userId)->get();
        }

        return $query->get();
    }

    public function findByPhotoGroupIdWithLocations($photoGroupId, $userId = null)
    {
        $query = Photo::with('location')->where('photo_group_id', $photoGroupId)->whereHas('location');

        if ($userId) {
            return $query->where('user_id', $userId)->get();
        }

        return $query->get();
    }
}
