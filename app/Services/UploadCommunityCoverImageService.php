<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class UploadCommunityCoverImageService
{
    public function update($community, $request)
    {
        ! is_null($community->cover_image) && Storage::disk(getCurrentDisk())->delete($community->cover_image);

        $image = ImageManager::gd()->read($request->file('image'));

        $file = $request->file('image');
        $extension = $file->getClientOriginalExtension();

        if ($request->width) {
            $image->crop(
                (int) $request->width,
                (int) $request->height,
                (int) $request->left,
                (int) $request->top
            );
        }

        if ((int) $request->width > 1200 || (int) $request->height > 300) {
            $image->resize(1200, 300);
        }

        $filename = strtolower($community->name).'-'.time().'.'.$extension;
        $path = 'covers/';
        $filePath = "$path$filename";

        Storage::disk(getCurrentDisk())->put($filePath, $image->toWebp(90)->toFilePointer());
        $community->cover_image = $filePath;

        return $community;
    }
}
