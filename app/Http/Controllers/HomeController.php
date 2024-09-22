<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessUpload;
use App\Enums\StatusType;
use App\Repositories\GpxRepository;
use App\Repositories\PhotoGroupRepository;
use App\Repositories\PhotoGroupStatusRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

class HomeController extends Controller
{
    protected PhotoGroupRepository $photoGroupRepository;
    protected PhotoGroupStatusRepository $photoGroupStatusRepository;
    protected GpxRepository $gpxRepository;

    /**
     * Create a new controller instance.
     *
     * @return void
     */
    public function __construct(PhotoGroupRepository $photoGroupRepository, PhotoGroupStatusRepository $photoGroupStatusRepository, GpxRepository $gpxRepository)
    {
        $this->middleware('auth');
        $this->photoGroupRepository = $photoGroupRepository;
        $this->photoGroupStatusRepository = $photoGroupStatusRepository;
        $this->gpxRepository = $gpxRepository;
    }

    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Contracts\Support\Renderable
     */
    public function index()
    {
        $userId = Auth::id();

        $photoGroups = $this->photoGroupRepository->getAllWithLastStatuses($userId);
        return view('home', ['photoGroups' => $photoGroups]);
    }

    public function upload(Request $request)
    {
        // ユーザIDを取得
        $userId = Auth::id();

        Log::debug($request);
     
        // バリデーション
        $request->validate([
            'photoGroupName' => 'required|string|max:255',
            'zipFile' => 'required|file|mimes:zip',
            'gpxFile' => 'required|file|mimetypes:application/gpx+xml,application/xml,text/xml',
        ]);

        // 写真グループ名を取得
        $photoGroupName = $request->input('photoGroupName');

        // ZIPファイルを保存
        $zipFile = $request->file('zipFile');
        $zipFilePath = $zipFile->store('temp/zip');
        $zipFileName = $zipFile->getClientOriginalName();

        // 写真グループをDB保存
        $photoGroupId = $this
            ->photoGroupRepository
            ->create($userId, '1', $zipFileName, $photoGroupName)
            ->id;

        // GPXファイルを保存
        $gpxFile = $request->file('gpxFile');
        $gpxFilePath = $gpxFile->store('temp/gpx');

        // ステータス:処理待ち
        $this->photoGroupStatusRepository->create($photoGroupId, StatusType::INACTIVE->value, '処理待ち');

        // 非同期でアップロード処理
        ProcessUpload::dispatch($userId, $photoGroupId, $zipFilePath, $gpxFilePath);

        return response()->json(['message' => '正常にアップロードされ、位置情報紐づけ処理が開始されました。']);
    }
}
