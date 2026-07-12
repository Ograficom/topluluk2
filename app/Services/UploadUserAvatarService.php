<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Intervention\Image\ImageManager;

class UploadUserAvatarService
{
    public function update($user, $request)
    {
        ! is_null($user->avatar) && Storage::disk(getCurrentDisk())->delete($user->avatar);

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

        $filename = $user->username.'-'.time().'.'.$extension;
        $path = 'avatars/';
        $filePath = "$path$filename";

        Storage::disk(getCurrentDisk())->put($filePath, $image->toWebp(90)->toFilePointer());
        $user->avatar = $filePath;

        return $user;
    }
}
