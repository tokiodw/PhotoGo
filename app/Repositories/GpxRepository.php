<?php

namespace App\Repositories;

use App\Models\Gpx;

class GpxRepository
{
    public function create($userId, $fileName, $storageDir)
    {
        return Gpx::create([
            'user_id' => $userId,
            'original_name' => $fileName,
            'storage_dir' => $storageDir,
        ]);
    }


}
