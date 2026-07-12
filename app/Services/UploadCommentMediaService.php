<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

class UploadCommentMediaService
{
    public function update($comment, $request)
    {
        ! is_null($comment->media_path) && Storage::disk(getCurrentDisk())->delete($comment->media_path);

        $file = $request->file('media');
        $mime = $file->getClientMimeType();
        $extension = $file->getClientOriginalExtension();

        if (strstr($mime, 'video/')) {
            $name = time().'.'.$extension;
            $filePath = $file->storeAs('/comments/video', $name, getCurrentDisk());
        } elseif (strstr($mime, 'image/')) {
            $name = time().'.'.$extension;
            $filePath = $file->storeAs('/comments/img', $name, getCurrentDisk());
        }

        $comment->media_path = $filePath;
        $comment->media_mime_type = $mime;

        return $comment;
    }
}
