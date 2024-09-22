<?php

namespace App\Http\Controllers;

use App\Repositories\PhotoGroupRepository;
use App\Repositories\PhotoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;

class MapController extends Controller
{
    protected PhotoRepository $photoRepository;
    protected PhotoGroupRepository $photoGroupRepository;
    //
    public function __construct(PhotoRepository $photoRepository, PhotoGroupRepository $photoGroupRepository)
    {
        $this->middleware('auth');
        $this->photoRepository = $photoRepository;
        $this->photoGroupRepository = $photoGroupRepository;
    }

    public function index($id)
    {
        $userId = Auth::id();
        $photoGroup = $this->photoGroupRepository->findByIdIfStatusIsSuccess($id);
        // $photoGroup = $this->photoGroupRepository->findByIdIfStatusIsSuccess($id, $userId);

        if (!$photoGroup) {
            return redirect()->back()->with('error', '紐づけ済の指定された画像グループが見つかりません.');
        }

        return view('map');
    }

    public function getPhotosJson($id)
    {
        $userId = Auth::id();
        $photos = $this->photoRepository->findByPhotoGroupIdWithLocations($id)
        // $photos = $this->photoRepository->findByPhotoGroupIdWithLocations($id, $userId)
            ->map(function ($photo) {
                $photoUrl = Storage::disk('s3f')->temporaryUrl($photo->storage_dir . '/' . $photo->original_name, now()->addHour());
                $photoThumbNailUrl = Storage::disk('s3f')->temporaryUrl($photo->thumbnail_dir . '/' . $photo->original_name, now()->addHour());

                $search = 'http://minio:9000';
                $replace = 'http://106.73.27.0:50804';
                $photoUrl = str_replace($search, $replace, $photoUrl);
                $photoThumbNailUrl = str_replace($search, $replace, $photoThumbNailUrl);
                $photo->url = $photoUrl;
                $photo->thumbnail_url = $photoThumbNailUrl;
                return $photo;
            });

        return response()->json($photos);
    }
}
