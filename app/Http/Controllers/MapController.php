<?php

namespace App\Http\Controllers;

use App\Models\PhotoGroup;
use App\Repositories\PhotoGroupRepository;
use App\Repositories\PhotoRepository;
use Illuminate\Support\Facades\Auth;
use Illuminate\Http\Request;

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
        $photoGroup = $this->photoGroupRepository->findById($id);

        if (!$photoGroup) {
            return redirect()->back()->with('error', '指定された画像グループが見つかりません.');
        }

        $photos = $photoGroup->photos;

        return view('map.index', ['photos' => $photos]);
    }
}
