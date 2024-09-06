<?php

namespace App\Repositories;

use App\Models\PhotoGroup;
use App\Models\PhotoGroupStatus;

class PhotoGroupRepository
{
    public function create($userId, $groupType, $zipFileName, $photoGroupName)
    {
        return PhotoGroup::create([
            'user_id' => $userId,
            'group_type' => $groupType,
            'file_name' => $zipFileName,
            'group_name' => $photoGroupName,
        ]);
    }

    public function updateCounts($photoGroupId, $photoCount, $nonPhotoCount): bool
    {
        return PhotoGroup::where('id', $photoGroupId)->update([
            'photo_count' => $photoCount,
            'non_photo_count' => $nonPhotoCount,
        ]);
    }

    public function getAllWithLastStatuses()
    {
        return PhotoGroup::leftJoinSub(
            PhotoGroupStatus::select('photo_group_id', 'status_type', 'status')
                ->whereIn(
                    'id',
                    PhotoGroupStatus::selectRaw('MAX(id)')
                        ->groupBy('photo_group_id')
                ),
            'latest_status',
            'photo_groups.id',
            'latest_status.photo_group_id'
        )
            ->select('photo_groups.*', 'latest_status.status_type as status_type', 'latest_status.status as status')
            ->orderBy('id', 'desc')
            ->get();
    }
}
