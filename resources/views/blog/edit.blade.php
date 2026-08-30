@php
    /*
     * Edit ekranı ayrı bir tasarım taşımıyor. Create composer'ı doğrudan kullanıyoruz;
     * yalnızca mevcut gönderinin değerlerini create formunun old() alanlarına besliyoruz.
     * Böylece /blog/create ile /edit görünümü tek kaynaktan birebir aynı kalır.
     */
    if (empty(session()->getOldInput())) {
        session()->flashInput([
            'title' => $post->title,
            'category_id' => $post->category_id,
            'tags' => $post->tags->pluck('id')->map(fn ($id) => (int) $id)->all(),
            'content' => $post->content,
            'content_json' => is_array($post->content_json)
                ? json_encode($post->content_json, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES)
                : (string) ($post->content_json ?? ''),
            'excerpt' => $post->excerpt,
            'meta_title' => $post->meta_title,
            'meta_description' => $post->meta_description,
            'meta_keywords' => $post->meta_keywords,
            'slug' => $post->slug,
            'published_at' => $post->published_at?->format('Y-m-d\\TH:i'),
            'image_license_url' => $post->image_license_url,
            'image_acquire_url' => $post->image_acquire_url,
            'image_credit_text' => $post->image_credit_text,
            'image_creator_name' => $post->image_creator_name,
            'image_copyright_notice' => $post->image_copyright_notice,
            'is_published' => $post->is_published ? 1 : 0,
            'comments_disabled' => $post->comments_disabled ? 1 : 0,
            'is_nsfw' => $post->is_nsfw ? 1 : 0,
            'is_pinned' => $post->is_pinned ? 1 : 0,
        ]);
    }

    $editAction = route('blog.post.update', $post);
    $existingFeaturedImage = (string) ($post->featured_image_url ?? '');
@endphp

@push('scripts')
<script data-edit-create-composer-integration>
    document.addEventListener('DOMContentLoaded', () => {
        const form = document.getElementById('post-create-form');
        if (!form) return;

        document.title = 'Gönderiyi düzenle';
        form.action = @js($editAction);
        form.dataset.editMode = '1';

        let methodInput = form.querySelector('input[name="_method"]');
        if (!methodInput) {
            methodInput = document.createElement('input');
            methodInput.type = 'hidden';
            methodInput.name = '_method';
            form.prepend(methodInput);
        }
        methodInput.value = 'PUT';

        const heading = document.querySelector('.create-page-fixed header .truncate.text-sm.font-semibold.text-slate-950');
        if (heading) heading.textContent = 'Gönderiyi düzenle';

        document.querySelectorAll('[data-submit-intent="publish"]').forEach((button) => {
            const label = button.querySelector('span');
            if (label) {
                label.textContent = 'Güncelle';
            } else {
                button.textContent = 'Güncelle';
            }
        });

        const existingFeaturedImage = @js($existingFeaturedImage);
        if (existingFeaturedImage) {
            const coverField = document.querySelector('[data-cover-field]');
            const coverPreview = document.querySelector('[data-cover-preview-img]');
            if (coverField && coverPreview) {
                coverPreview.src = existingFeaturedImage;
                coverField.classList.add('has-image');
            }
        }
    });
</script>
@endpush

@include('blog.create', [
    'categories' => $categories,
    'tags' => $tags,
    'reactionTypes' => $reactionTypes,
    'post' => $post,
])
