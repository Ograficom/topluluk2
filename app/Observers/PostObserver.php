<?php

declare(strict_types=1);

namespace App\Observers;

use App\Models\Post;
use App\Services\PostPublicationGuard;
use Illuminate\Validation\ValidationException;

class PostObserver
{
    public function saving(Post $post): void
    {
        $matched = app(PostPublicationGuard::class)->findBannedWord($post);
        if ($matched === null) {
            return;
        }

        // Yeni gönderi hiç oluşturulmaz. Mevcut gönderi düzenlenirken yasaklı
        // kelime eklenirse mevcut kayıt da taslakta/yayında bırakılmaz.
        if ($post->exists) {
            $post->delete();
        }

        throw ValidationException::withMessages([
            'title' => 'Gönderi yasaklı kelime/ifade içerdiği için kaydedilmedi ve sistemden silindi.',
        ]);
    }
}
