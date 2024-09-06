<?php

namespace App\Jobs;

use App\Enums\StatusType;
use App\Repositories\GpxRepository;
use App\Repositories\PhotoGroupRepository;
use App\Repositories\PhotoGroupStatusRepository;
use App\Repositories\PhotoLocationRepository;
use App\Repositories\PhotoRepository;
use DateTime;
use Exception;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Log;
use ZipArchive;

class ProcessUpload implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $userId;
    protected $photoGroupId;
    protected $zipFilePath;
    protected $gpxFilePath;

    protected PhotoRepository $photoRepository;
    protected PhotoGroupRepository $photoGroupRepository;
    protected PhotoGroupStatusRepository $photoGroupStatusRepository;
    protected PhotoLocationRepository $photoLocationRepository;
    protected GpxRepository $gpxRepository;

    protected const PHOTO_EXTENSIONS = ['jpg', 'jpeg', 'png'];
    protected const TEMP_DIR = 'temp/extracted/';
    protected const UPLOAD_PHOTO_DIR = 'uploads/photo/';
    protected const UPLOAD_GPX_DIR = 'uploads/gpx/';
    protected const MAX_ALLOWED_DIFF = '300';

    /**
     * Create a new job instance.
     */
    public function __construct($userId, $photoGroupId, $zipFilePath, $gpxFilePath)
    {
        $this->userId = $userId;
        $this->photoGroupId = $photoGroupId;
        $this->zipFilePath = $zipFilePath;
        $this->gpxFilePath = $gpxFilePath;
    }

    /**
     * Execute the job.
     */
    public function handle(
        PhotoRepository $photoRepository,
        PhotoGroupRepository $photoGroupRepository,
        PhotoGroupStatusRepository $photoGroupStatusRepository,
        PhotoLocationRepository $photoLocationRepository,
        GpxRepository $gpxRepository,
    ): void {
        // 依存性注入
        $this->photoRepository = $photoRepository;
        $this->photoGroupRepository = $photoGroupRepository;
        $this->photoGroupStatusRepository = $photoGroupStatusRepository;
        $this->photoLocationRepository = $photoLocationRepository;
        $this->gpxRepository = $gpxRepository;

        // ステータス:処理開始
        $this->updateStatus(StatusType::ACTIVE->value, '処理開始');
        
        // 格納用のランダム値を生成
        $uniqueId = uniqid();

        // 解凍先とストレージ先のパスを設定
        $extractedDir = self::TEMP_DIR . $uniqueId;
        $storagePhotoDir = self::UPLOAD_PHOTO_DIR . $uniqueId;
        $stroageGpxDir = self::UPLOAD_GPX_DIR . $uniqueId;

        try {

            // ステータス:解凍中
            $this->updateStatus(StatusType::ACTIVE->value, '解凍中');
            // 解凍できなかったら処理終了
            if (!$this->unZip($extractedDir)) {
                $this->updateStatus(StatusType::ERROR->value, '解凍失敗');
                return;
            }

            // ステータス:画像保存中
            $this->updateStatus(StatusType::ACTIVE->value, '画像保存中');

            // 各画像のアップロード処理開始
            [$photoCount, $nonPhotoCount] = $this->startUploadPhotos($extractedDir, $storagePhotoDir);

            // photoGroupテーブルの更新
            $this->photoGroupRepository->updateCounts($this->photoGroupId, $photoCount, $nonPhotoCount);

            // ステータス：GPX保存中
            $this->updateStatus(StatusType::ACTIVE->value, 'GPX保存中');

            // GPX保存
            $gpxId = $this->uploadGpx($stroageGpxDir);

            // ステータス:GX解析中
            $this->updateStatus(StatusType::ACTIVE->value, 'GPX解析中');

            // GPX解析
            $tracks = $this->parseGpxFile();

            // ステータス:GPX紐づけ中
            $this->updateStatus(StatusType::ACTIVE->value, 'GPX紐づけ中');

            // GPX紐づけ処理の開始
            $this->associatePhotoAndGpx($gpxId, $tracks);

            // ステータス:完了
            $this->updateStatus(StatusType::SUCCESS->value, '完了');
        } catch (Exception $e) {
            // ステータス:エラー発生
            $this->updateStatus(StatusType::ERROR->value, 'エラー発生');
            Log::error('ProcessUploadにてエラーが発生しました。： ' . $e->getMessage());
        } finally {
            // Tempディレクトリ内を削除
            $this->cleanup($extractedDir);
        }
    }

    private function unZip($extractedDir): bool
    {
        $zip = new ZipArchive;

        // zipファイルをオープン出来なかったら処理終了
        if ($zip->open(Storage::path($this->zipFilePath)) !== true) {
            return false;
        }

        // zipファイルを所定のディレクトリに解凍
        $zip->extractTo(Storage::path($extractedDir));
        $zip->close();

        return true;
    }

    private function startUploadPhotos($extractedDir, $storagePhotoDir): array
    {
        // 解凍ディレクトリ内の全ファイルを取得
        $files = Storage::allFiles($extractedDir);
        $photoCount = 0;
        $nonPhotoCount = 0;

        foreach ($files as $file) {
            // 対象ファイルの拡張子を抽出
            $extension = strtolower(pathinfo($file, PATHINFO_EXTENSION));

            // 画像ファイル判定
            if ($this->isPhoto($extension)) {
                $photoCount++;
                // ストレージへアップロード開始
                $this->uploadPhoto($file, $storagePhotoDir);
            } else {
                $nonPhotoCount++;
            }
        }

        return [$photoCount, $nonPhotoCount];
    }

    private function uploadPhoto($file, $storagePhotoDir): void
    {
        $relativeFilePath = Storage::path($file);
        $fileName = basename($file);
        $storagePath = $storagePhotoDir . '/' . $fileName;

        // exif情報を取得
        $exifData = @exif_read_data($relativeFilePath);
        
        // 撮影日時を取得
        $takenAt = isset($exifData['DateTimeOriginal']) ? date('Y-m-d H:i:s', strtotime($exifData['DateTimeOriginal'])) : null;

        // MinIOにアップロード
        Storage::disk('s3')->put($storagePath, file_get_contents($relativeFilePath));

        // アップロードが完了したら、DBに情報を登録する。
        $this->photoRepository->create($this->userId, $this->photoGroupId, $fileName, $storagePhotoDir, $takenAt);
    }

    private function isPhoto(string $extension): bool
    {
        return in_array($extension, self::PHOTO_EXTENSIONS);
    }

    private function uploadGpx($stroageGpxDir): int {
        $relativeFilePath = Storage::path($this->gpxFilePath);
        $fileName = basename($this->gpxFilePath);
        $storagePath = $stroageGpxDir . '/' . $fileName;

        Storage::disk('s3')->put($storagePath, file_get_contents($relativeFilePath));

        return $this->gpxRepository->create($this->userId, $fileName, $stroageGpxDir)->id;
    }

    private function parseGpxFile(): array {
        $xml = simplexml_load_file(Storage::path($this->gpxFilePath));
        $tracks = [];
        foreach ($xml->trk->trkseg->trkpt as $trkpt) { 
            $tracks[] = [
                'lat' => (float) $trkpt['lat'],
                'lon' => (float) $trkpt['lon'],
                'time' => (string) $trkpt->time,
            ];
        }
        return $tracks;
    }

    private function associatePhotoAndGpx($gpxId, $tracks) {
        // 登録した画像を全て取得
        $photos = $this->photoRepository->findByPhotoGroupId($this->photoGroupId);

        foreach ($photos as $photo) {
            $takenAt = new DateTime($photo->taken_at);
            $closestTrack = $this->findClosestTrack($takenAt, $tracks);

            // 紐づけが出来たらDBに登録
            if ($closestTrack) {
                $photoId = $photo->id;
                $latitude = $closestTrack['lat'];
                $longitude = $closestTrack['lon'];
                $this->photoLocationRepository->create($photoId, $gpxId, $latitude, $longitude);
            }
        }
    }

    private function findClosestTrack($takenAt, $tracks) {
        $closestTrack = null;
        $minDiff = PHP_INT_MAX;

        foreach ($tracks as $track) {
            Log::debug($track['time']);
            $trackTime = new DateTime($track['time']);
            
            $diff = abs($trackTime->getTimestamp() - $takenAt->getTimestamp());
            // 許容時間を超えた場合は対象外とする
            if ($diff > self::MAX_ALLOWED_DIFF) {
                continue;
            }

            if ($diff < $minDiff) {
                $minDiff = $diff;
                $closestTrack = $track;
            }
        }
        return $closestTrack;
    }

    private function updateStatus($status_type, $status): void
    {
        $this->photoGroupStatusRepository->create($this->photoGroupId, $status_type, $status);
    }

    private function cleanup($extractedDir): void
    {
        Storage::delete($this->zipFilePath);
        Storage::deleteDirectory($extractedDir);
    }
}
