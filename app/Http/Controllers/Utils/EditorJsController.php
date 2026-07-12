<?php

namespace App\Http\Controllers\Utils;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class EditorJsController extends Controller
{
    public function uploadImage(Request $request): JsonResponse
    {
        $uploadLimit = 2 * 1024 * 1024;

        if ($request->image->getSize() > $uploadLimit) {
            return response()->json([
                'error' => 1,
            ]);
        }

        $imageName = time().'.'.$request->image->extension();
        $imageFileUrl = $request->image->storeAs('/stories/img', $imageName, getCurrentDisk());

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk(getCurrentDisk())->url($imageFileUrl),
            ],
        ]);
    }

    public function uploadVideo(Request $request): JsonResponse
    {
        $uploadLimit = 10 * 1024 * 1024;

        if ($request->video->getSize() > $uploadLimit) {
            return response()->json([
                'error' => 1,
            ]);
        }

        $videoName = time().'.'.$request->video->extension();
        $videoFileUrl = $request->video->storeAs('/stories/video', $videoName, getCurrentDisk());

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk(getCurrentDisk())->url($videoFileUrl),
            ],
        ]);
    }

    public function uploadAudio(Request $request): JsonResponse
    {
        $uploadLimit = 10 * 1024 * 1024;

        if ($request->audio->getSize() > $uploadLimit) {
            return response()->json([
                'error' => 1,
            ]);
        }

        $audioName = time().'.'.$request->audio->extension();
        $audioFileUrl = $request->audio->storeAs('/stories/audio', $audioName, getCurrentDisk());

        return response()->json([
            'success' => 1,
            'file' => [
                'url' => Storage::disk(getCurrentDisk())->url($audioFileUrl),
            ],
        ]);
    }

    public function deleteUploadedMedia(Request $request)
    {
        if (getCurrentDisk() === 'local') {
            $full_path = parse_url($request->url, PHP_URL_PATH);
            $path = str_replace('/uploads', '', $full_path);

            if (Storage::disk(getCurrentDisk())->exists($path)) {
                Storage::disk(getCurrentDisk())->delete($path);

                return response()->json(['success' => 'Deleted successfully!']);
            } else {
                return response()->json(['error' => 'File not exists!']);
            }
        } else {
            $path = parse_url($request->url, PHP_URL_PATH);

            if (Storage::disk(getCurrentDisk())->exists($path)) {
                Storage::disk(getCurrentDisk())->delete($path);

                return response()->json(['success' => 'Deleted successfully!']);
            } else {
                return response()->json(['error' => 'File not exists!']);
            }
        }
    }
}
