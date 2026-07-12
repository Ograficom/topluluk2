<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class UploadUserCoverImageService
{
    public function update($user, $request)
    {
        ! is_null($user->cover_image) && Storage::disk(getCurrentDisk())->delete($user->cover_image);

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

        $filename = $user->username.'-'.time().'.'.$extension;
        $path = 'covers/';
        $filePath = "$path$filename";

        Storage::disk(getCurrentDisk())->put($filePath, $image->toWebp(90)->toFilePointer());
        $user->cover_image = $filePath;

        return $user;
    }
}
