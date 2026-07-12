<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class UploadCommunityAvatarService
{
    public function update($community, $request)
    {
        ! is_null($community->avatar) && Storage::disk(getCurrentDisk())->delete($community->avatar);

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

        if ((int) $request->width > 300 || (int) $request->height > 300) {
            $image->resize(300, 300);
        }

        $filename = strtolower($community->name).'-'.time().'.'.$extension;
        $path = 'avatars/';
        $filePath = "$path$filename";

        Storage::disk(getCurrentDisk())->put($filePath, $image->toWebp(90)->toFilePointer());
        $community->avatar = $filePath;

        return $community;
    }
}
