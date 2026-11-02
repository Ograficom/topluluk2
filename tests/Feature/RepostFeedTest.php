<?php

namespace Tests\Feature;

use App\Filament\Pages\SiteBranding;
use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\PostReaction;
use App\Models\SiteSetting;
use App\Models\User;
use App\Support\SvgSanitizer;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class RepostFeedTest extends TestCase
{
    use RefreshDatabase;

    public function test_feed_lists_published_posts_and_searches_them(): void
    {
        $channel = Channel::create([
            'name' => 'Teknoloji',
            'slug' => 'teknoloji',
            'is_active' => true,
        ]);
        $published = $this->createPost($channel, [
            'title' => 'Aranabilir bir hikâye',
            'slug' => 'aranabilir-hikaye',
        ]);
        $this->createPost($channel, [
            'title' => 'Taslak yazı',
            'slug' => 'taslak-yazi',
            'is_published' => false,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Taslak yazı');

        $this->get('/?q=aranabilir')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Taslak yazı');
    }

    public function test_feed_uses_the_template_digest_show_more_and_active_filters(): void
    {
        $channel = Channel::create([
            'name' => 'Akış şablonu',
            'slug' => 'akis-sablonu',
            'is_active' => true,
        ]);

        $posts = [];
        foreach (range(1, 11) as $number) {
            $posts[] = $this->createPost($channel, [
                'title' => "Şablon yazısı {$number}",
                'slug' => "sablon-yazisi-{$number}",
            ]);
        }

        foreach (['like', 'like', 'dislike'] as $reaction) {
            PostReaction::create([
                'post_id' => $posts[0]->id,
                'user_id' => User::factory()->create()->id,
                'type' => $reaction,
            ]);
        }

        $filteredFeed = $this->get(route('posts.index', ['sort' => 'popular', 'period' => 'week']))
            ->assertOk()
            ->assertSee('<aside class="main__widgets"', false)
            ->assertDontSee('<aside class="profile__aside"', false)
            ->assertSee('data-loadpost="last"', false)
            ->assertSee('data-loadpost-limit="5"', false)
            ->assertSee('data-loadpost-more="last"', false)
            ->assertSee('data-loadpost-item="last"', false)
            ->assertSee('class="post__short-title-text"', false)
            ->assertSee('class="post__footer-stats small color-gray"', false)
            ->assertSee('class="post__options card-body"', false)
            ->assertSee('<span class="post__options-count">+1</span>', false)
            ->assertSee('<nav class="navigation"', false)
            ->assertSee('Daha fazla göster')
            ->assertSee('page=2', false)
            ->assertSee('Bu hafta')
            ->assertSee('is-active', false);

        $this->assertStringContainsString(
            '<li class="dropdown-menu-item dropdown-menu-item-sm is-active"><a href="'.route('posts.index', ['period' => 'week']).'">Bu hafta</a></li>',
            $filteredFeed->getContent(),
        );

        $this->get(route('posts.index', ['sort' => 'latest', 'q' => 'Şablon yazısı 1']))
            ->assertOk()
            ->assertSee('>Tüm zamanlar</a>', false)
            ->assertDontSee('data-loadpost-more="last"', false);

        $this->get(route('posts.index', ['sort' => 'popular', 'period' => 'week', 'page' => 2]))
            ->assertOk()
            ->assertSee('Önceki')
            ->assertSee('rel="prev"', false)
            ->assertDontSee('rel="next"', false);
    }

    public function test_homepage_shows_popular_comments_and_admin_category_shortcut(): void
    {
        $channel = Channel::create([
            'name' => 'Topluluk',
            'slug' => 'topluluk',
            'is_active' => true,
        ]);
        $popularPost = $this->createPost($channel, [
            'title' => 'Çok yorum alan yazı',
            'slug' => 'cok-yorum-alan-yazi',
        ]);
        $otherPost = $this->createPost($channel, [
            'title' => 'Diğer yazı',
            'slug' => 'diger-yazi',
        ]);
        $commenter = User::factory()->create();

        Comment::create([
            'post_id' => $popularPost->id,
            'user_id' => $commenter->id,
            'body' => 'Popüler yazıdaki yorum.',
        ]);
        Comment::create([
            'post_id' => $popularPost->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Popüler yazıdaki ikinci yorum.',
        ]);
        Comment::create([
            'post_id' => $otherPost->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Daha az popüler yazıdaki yorum.',
        ]);

        $admin = User::factory()->create(['is_admin' => true]);
        $response = $this->actingAs($admin)->get('/');

        $response->assertOk()
            ->assertSee('Popüler yorumlar')
            ->assertSee('Popüler yazıdaki yorum.')
            ->assertSee('Daha az popüler yazıdaki yorum.')
            ->assertSee(route('channels.create'));

        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, 'Popüler yazıdaki yorum.') < strpos($content, 'Daha az popüler yazıdaki yorum.'),
        );

        auth()->logout();
        $this->get('/')
            ->assertOk()
            ->assertDontSee(route('channels.create'));
    }

    public function test_members_page_lists_members_by_published_post_count(): void
    {
        $channel = Channel::create([
            'name' => 'Üyeler',
            'slug' => 'uyeler',
            'is_active' => true,
        ]);
        $topMember = User::factory()->create(['name' => 'Önde gelen üye']);
        $newMember = User::factory()->create(['name' => 'Yeni üye', 'username' => null]);

        $this->createPost($channel, [
            'user_id' => $topMember->id,
            'title' => 'Üyenin ilk yazısı',
            'slug' => 'uyenin-ilk-yazisi',
        ]);
        $this->createPost($channel, [
            'user_id' => $topMember->id,
            'title' => 'Üyenin ikinci yazısı',
            'slug' => 'uyenin-ikinci-yazisi',
        ]);
        $response = $this->get(route('members.index'))
            ->assertOk()
            ->assertSee('user-rating__list', false)
            ->assertSee('Yazar sıralaması')
            ->assertSee('Önde gelen üye')
            ->assertSee('Yeni üye')
            ->assertSee(route('profiles.show', $newMember), false);

        $content = $response->getContent();
        $this->assertTrue(
            strpos($content, 'Önde gelen üye') < strpos($content, 'Yeni üye'),
        );
        $response->assertSee('2 yazı')->assertDontSee('3 yazı');
    }

    public function test_statistics_page_uses_real_counts_and_original_chart_markup(): void
    {
        $channel = Channel::create([
            'name' => 'İstatistikler',
            'slug' => 'istatistikler',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'title' => 'İstatistik yazısı',
            'slug' => 'istatistik-yazisi',
        ]);
        $this->createPost($channel, [
            'title' => 'Taslak istatistik yazısı',
            'slug' => 'taslak-istatistik-yazisi',
            'is_published' => false,
        ]);
        $commenter = User::factory()->create();
        Comment::create([
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'body' => 'İstatistik yorumu',
        ]);

        $this->get(route('stats.index'))
            ->assertOk()
            ->assertSee('<article class="feed">', false)
            ->assertSee('class="main__widgets"', false)
            ->assertSee('class="widget__content tagscloud"', false)
            ->assertSee('href="'.route('posts.index', ['q' => 'örnek']).'"', false)
            ->assertSee('stats__counts', false)
            ->assertSee('statsTimelineChart', false)
            ->assertSee('statsPostsChart', false)
            ->assertSee('data-news-num="2"', false)
            ->assertSee('data-news-allow="1"', false)
            ->assertSee('stats__count-num">2</span>', false)
            ->assertSee('data-comm-num="1"', false)
            ->assertDontSee('Çevrimiçi kullanıcı ve yasaklı hesap sayıları bu projede takip edilmiyor.')
            ->assertSee(route('stats.index'), false);
    }

    public function test_empty_category_list_does_not_render_a_fake_category_card(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);

        $this->actingAs($admin)
            ->get('/channels')
            ->assertOk()
            ->assertSee('Henüz kategori yok.')
            ->assertSee(route('channels.create'), false)
            ->assertDontSee('<article class="subchannel">', false);
    }

    public function test_invalid_post_cover_is_hidden_and_uploaded_cover_uses_current_site_url(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/valid-cover.png', 'test image');

        $channel = Channel::create([
            'name' => 'Görseller',
            'slug' => 'gorseller',
            'is_active' => true,
        ]);
        $this->createPost($channel, [
            'title' => 'Geçersiz kapak',
            'slug' => 'gecersiz-kapak',
            'cover_image' => 'merhaba',
        ]);
        $validPost = $this->createPost($channel, [
            'title' => 'Geçerli kapak',
            'slug' => 'gecerli-kapak',
            'cover_image' => 'posts/valid-cover.png',
        ]);

        $response = $this->get('/');
        $response->assertOk()
            ->assertSee(asset('storage/posts/valid-cover.png'), false);
        $this->assertStringNotContainsString('src="'.asset('merhaba').'"', $response->getContent());
        $this->assertSame(
            asset('storage/posts/valid-cover.png'),
            $validPost->coverImageUrl(),
        );
    }

    public function test_logout_button_uses_the_original_dropdown_menu_control_styles(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee('<button type="submit" form="logoutForm">', false)
            ->assertSee('<li class="dropdown-menu-item dropdown-menu-item-lg is-danger">', false);
    }

    public function test_post_detail_increments_views_and_does_not_expose_drafts(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('posts/detail-cover.jpg', 'cover');
        $channel = Channel::create([
            'name' => 'Yolculuk',
            'slug' => 'yolculuk',
            'is_active' => true,
        ]);
        $published = $this->createPost($channel, [
            'title' => 'Yol hikâyesi',
            'slug' => 'yol-hikayesi',
            'excerpt' => 'Kısa giriş metni.',
            'body' => "Yazının ilk paragrafı.\n\nYazının ikinci paragrafı.",
            'cover_image' => 'posts/detail-cover.jpg',
        ]);
        $draft = $this->createPost($channel, [
            'title' => 'Gizli taslak',
            'slug' => 'gizli-taslak',
            'is_published' => false,
        ]);
        Comment::create([
            'post_id' => $published->id,
            'user_id' => User::factory()->create()->id,
            'body' => "İlk yorum.\nİkinci satır.",
        ]);

        $this->get('/posts/yol-hikayesi')
            ->assertOk()
            ->assertSeeInOrder([
                'class="post__header"',
                'href="'.asset('storage/posts/detail-cover.jpg').'" data-fancybox="full-post"',
                'class="post__title-full"',
                'class="editor"',
                '<p>Yazının ilk paragrafı.</p>',
                '<p>Yazının ikinci paragrafı.</p>',
                'class="post__tags post__tags-full"',
                'class="post__footer post__footer-full"',
                'class="post__options card-body"',
                'aria-label="Favorilere eklemek için giriş yap"',
                'id="comments"',
            ], false)
            ->assertSee('Yorumlar (1)')
            ->assertSee('Yorum yazabilmek için giriş yapın')
            ->assertSee('Giriş yap ve yorum yaz')
            ->assertSee('İlk yorum.')
            ->assertSee('İkinci satır.')
            ->assertSee('Yorumu beğenmek için giriş yapın')
            ->assertSee('Yanıtla')
            ->assertSee('Yazı menüsünü aç')
            ->assertSee('Yorum menüsünü aç');

        $this->assertDatabaseHas('posts', ['id' => $published->id, 'views' => 1]);
        $this->get('/posts/gizli-taslak')->assertNotFound();
        $this->assertDatabaseHas('posts', ['id' => $draft->id, 'views' => 0]);
    }

    public function test_members_can_react_and_comment_on_a_published_post(): void
    {
        $user = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Topluluk',
            'slug' => 'topluluk',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'title' => 'Etkileşim testi',
            'slug' => 'etkilesim-testi',
        ]);

        $this->actingAs($user)
            ->post('/posts/'.$post->slug.'/reactions/like')
            ->assertRedirect();
        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'type' => 'like',
        ]);

        $this->post('/posts/'.$post->slug.'/reactions/dislike')
            ->assertRedirect();
        $this->assertDatabaseHas('post_reactions', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'type' => 'dislike',
        ]);
        $this->assertDatabaseCount('post_reactions', 1);

        $response = $this->post('/posts/'.$post->slug.'/comments', ['body' => 'Güzel paylaşım.']);
        $comment = Comment::query()->where('body', 'Güzel paylaşım.')->firstOrFail();
        $response->assertRedirect(route('posts.show', $post).'#comment-'.$comment->id);

        $this->assertDatabaseHas('comments', [
            'post_id' => $post->id,
            'user_id' => $user->id,
            'body' => 'Güzel paylaşım.',
        ]);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Güzel paylaşım.')
            ->assertSee('name="body"', false)
            ->assertSee('aria-label="Dislike" aria-pressed="true"', false);

        $this->get(route('profiles.show', $user->username))
            ->assertOk()
            ->assertSee('data-tab-panel="comments"', false)
            ->assertSee('Güzel paylaşım.')
            ->assertSee($post->title);
    }

    public function test_members_can_like_comments_and_reply_to_them(): void
    {
        $author = User::factory()->create();
        $member = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Yorumlar',
            'slug' => 'yorumlar',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'user_id' => $author->id,
            'slug' => 'yorum-etkilesimi',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Ana yorum',
        ]);

        $this->actingAs($member)
            ->post(route('posts.comments.reactions.like', ['post' => $post, 'comment' => $comment]))
            ->assertRedirect();
        $this->assertDatabaseHas('comment_reactions', [
            'comment_id' => $comment->id,
            'user_id' => $member->id,
        ]);

        $this->post(route('posts.comments.store', $post), [
            'body' => 'Yoruma yanıt',
            'parent_id' => $comment->id,
        ])->assertRedirect(route('posts.show', $post).'#comment-'.Comment::query()->where('body', 'Yoruma yanıt')->value('id'));

        $reply = Comment::query()->where('body', 'Yoruma yanıt')->firstOrFail();
        $this->assertSame($comment->id, $reply->parent_id);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Ana yorum')
            ->assertSee('Yoruma yanıt')
            ->assertSee('1 yanıt')
            ->assertSee('aria-pressed="true"', false);

        $this->post(route('posts.comments.reactions.like', ['post' => $post, 'comment' => $comment]))
            ->assertRedirect();
        $this->assertDatabaseMissing('comment_reactions', [
            'comment_id' => $comment->id,
            'user_id' => $member->id,
        ]);
    }

    public function test_post_comments_use_a_safe_public_name_and_reference_button_style(): void
    {
        $author = User::factory()->create([
            'name' => 'enesbodur@ografi.com',
            'username' => 'enesbodur@ografi.com',
            'full_name' => null,
        ]);
        $member = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Yorum görünümü',
            'slug' => 'yorum-gorunumu',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'user_id' => $author->id,
            'slug' => 'yorum-gorunumu',
        ]);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => $author->id,
            'body' => 'Örnek yorum.',
        ]);

        $this->actingAs($member)
            ->get(route('posts.show', ['post' => $post, 'reply_to' => $comment->id]))
            ->assertOk()
            ->assertSee('enesbodur kullanıcısına yanıt yazıyorsunuz.')
            ->assertSee('>enesbodur</a>', false)
            ->assertDontSee('>enesbodur@ografi.com</a>', false)
            ->assertSee('aria-label="enesbodur"', false)
            ->assertDontSee('aria-label="enesbodur@ografi.com"', false)
            ->assertSee('btn-link comment__rating', false);

        $this->get(route('profiles.show', $author))
            ->assertOk()
            ->assertSee('@enesbodur')
            ->assertDontSee('@enesbodur@ografi.com');
    }

    public function test_members_can_upload_and_view_comment_images(): void
    {
        Storage::fake('public');
        $member = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Fotoğraflar',
            'slug' => 'fotograflar',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, ['slug' => 'yorum-fotograflari']);

        $this->actingAs($member)
            ->post(route('posts.comments.store', $post), [
                'body' => 'Yol fotoğrafı',
                'images' => [UploadedFile::fake()->image('road-trip.png')],
            ])
            ->assertRedirect();

        $comment = Comment::query()->where('body', 'Yol fotoğrafı')->firstOrFail();
        $imagePath = $comment->images[0];
        Storage::disk('public')->assertExists($imagePath);

        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('comments-image-gallery', false)
            ->assertSee(asset('storage/'.$imagePath), false)
            ->assertSee('type="file" name="images[]"', false);
    }

    public function test_admins_can_delete_selected_comments_and_their_images(): void
    {
        Storage::fake('public');
        Storage::disk('public')->put('comments/selected.png', 'image');
        $admin = User::factory()->create(['is_admin' => true]);
        $channel = Channel::create([
            'name' => 'Moderasyon',
            'slug' => 'moderasyon',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, ['slug' => 'yorum-moderasyonu']);
        $comment = Comment::create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'body' => 'Silinecek yorum',
            'images' => ['comments/selected.png'],
        ]);
        $reply = Comment::create([
            'post_id' => $post->id,
            'user_id' => User::factory()->create()->id,
            'parent_id' => $comment->id,
            'body' => 'Silinecek yanıt',
        ]);

        $this->actingAs($admin)
            ->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('comment-moderation-form', false)
            ->assertSee('name="selected_comments[]"', false);

        $this->post(route('posts.comments.moderate', $post), [
            'action' => 'delete',
            'selected_comments' => [$comment->id],
        ])
            ->assertRedirect(route('posts.show', $post).'#comments')
            ->assertSessionHas('status', 'Seçilen yorumlar silindi.');

        $this->assertDatabaseMissing('comments', ['id' => $comment->id]);
        $this->assertDatabaseMissing('comments', ['id' => $reply->id]);
        Storage::disk('public')->assertMissing('comments/selected.png');
    }

    public function test_profile_uses_the_original_layout_and_supports_users_without_usernames(): void
    {
        $user = User::factory()->create([
            'username' => null,
            'bio' => 'Profil açıklama metni.',
        ]);
        $channel = Channel::create([
            'name' => 'Profil kanalı',
            'slug' => 'profil-kanali',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'user_id' => $user->id,
            'title' => 'Profil yazısı',
            'slug' => 'profil-yazisi',
        ]);

        $this->actingAs($user)
            ->get(route('profiles.show', $user))
            ->assertOk()
            ->assertSee('profile__aside', false)
            ->assertSee('profile-panel-posts', false)
            ->assertSee('class="post__short-title-text"', false)
            ->assertSee('class="post__options card-body"', false)
            ->assertSee('Profil açıklama metni.')
            ->assertSee('Profil yazısı')
            ->assertSee(route('profiles.show', $user), false);

        $this->get('/users/'.$user->id)
            ->assertOk()
            ->assertSee('Profil yazısı');
    }

    public function test_members_can_create_posts_from_the_original_style_form(): void
    {
        $author = User::factory()->create();
        $channel = Channel::create([
            'name' => 'Teknoloji',
            'slug' => 'teknoloji',
            'is_active' => true,
        ]);

        $this->actingAs($author)
            ->get('/posts/create')
            ->assertOk()
            ->assertSee('action="'.route('posts.store').'"', false)
            ->assertSee('class="main main__grid-full"', false)
            ->assertSee('class="addpost main__grid-feed"', false)
            ->assertDontSee('Popüler yazarlar')
            ->assertSee('name="title"', false)
            ->assertSee('name="body"', false);

        $this->post('/posts', [
            'title' => 'Yeni bir paylaşım',
            'channel_id' => $channel->id,
            'excerpt' => 'Yazının kısa açıklaması.',
            'body' => 'Yayınlanan yazının içeriği.',
            'tags' => 'laravel, php, laravel',
            'is_adult' => '1',
        ])->assertRedirect('/posts/yeni-bir-paylasim');

        $this->assertDatabaseHas('posts', [
            'user_id' => $author->id,
            'channel_id' => $channel->id,
            'slug' => 'yeni-bir-paylasim',
            'is_published' => true,
            'is_adult' => true,
            'tags' => json_encode(['laravel', 'php']),
        ]);
    }

    public function test_only_administrators_can_create_categories(): void
    {
        $member = User::factory()->create();

        $this->actingAs($member)
            ->get('/channels/create')
            ->assertForbidden();
        $this->post('/channels', ['name' => 'Üye kategorisi'])
            ->assertForbidden();
        $this->assertDatabaseMissing('channels', ['name' => 'Üye kategorisi']);

        Channel::create([
            'name' => 'Mevcut kategori',
            'slug' => 'mevcut-kategori',
            'is_active' => true,
        ]);
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin)
            ->get('/channels')
            ->assertOk()
            ->assertSee('subscriptions__section', false)
            ->assertSee('subscriptions__channels', false)
            ->assertSee('subchannel__name', false)
            ->assertSee(route('channels.create'), false);

        $this->get('/channels/create')
            ->assertOk()
            ->assertSee('action="'.route('channels.store').'"', false)
            ->assertSee('Kategori oluştur')
            ->assertSee('subscriptions__section', false)
            ->assertSee('setting__list-item', false);

        $this->post('/channels', [
            'name' => 'Film ve Dizi',
            'description' => 'Sinema ve televizyon üzerine.',
        ])->assertRedirect('/channels/film-ve-dizi');

        $this->assertDatabaseHas('channels', [
            'name' => 'Film ve Dizi',
            'slug' => 'film-ve-dizi',
            'description' => 'Sinema ve televizyon üzerine.',
            'is_active' => true,
        ]);
    }

    public function test_adult_posts_are_hidden_until_the_member_enables_them(): void
    {
        $channel = Channel::create([
            'name' => 'Yetişkin',
            'slug' => 'yetiskin',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'title' => 'Kısıtlı içerik',
            'slug' => 'kisitleme-icerik',
            'body' => 'Yetişkin yazı metni.',
            'is_adult' => true,
        ]);

        $this->get('/')
            ->assertOk()
            ->assertDontSee($post->title);
        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('18+ content')
            ->assertDontSee('Yetişkin yazı metni.');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'views' => 0]);

        $user = User::factory()->create(['show_adult_content' => true]);
        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertSee($post->title);
        $this->get(route('posts.show', $post))
            ->assertOk()
            ->assertSee('Yetişkin yazı metni.');
        $this->assertDatabaseHas('posts', ['id' => $post->id, 'views' => 1]);
    }

    public function test_channel_pages_only_show_active_channels_and_published_posts(): void
    {
        $channel = Channel::create([
            'name' => 'Tasarım',
            'slug' => 'tasarim',
            'is_active' => true,
        ]);
        Channel::create([
            'name' => 'Gizli kanal',
            'slug' => 'gizli-kanal',
            'is_active' => false,
        ]);
        $published = $this->createPost($channel, [
            'title' => 'Tasarım yazısı',
            'slug' => 'tasarim-yazisi',
        ]);
        $this->createPost($channel, [
            'title' => 'Taslak yazı',
            'slug' => 'tasarim-taslagi',
            'is_published' => false,
        ]);

        $this->get('/channels')
            ->assertOk()
            ->assertSee('Tasarım')
            ->assertDontSee('<article class="feed">', false)
            ->assertDontSee('Gizli kanal');

        $this->get('/channels/tasarim')
            ->assertOk()
            ->assertSee('channel-page__content', false)
            ->assertSee('channel__posts', false)
            ->assertSee($published->title)
            ->assertDontSee('Taslak yazı');

        $this->get('/channels/gizli-kanal')->assertNotFound();
    }

    public function test_only_administrators_can_open_the_filament_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
    }

    public function test_administrators_can_update_the_site_branding_from_filament(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(SiteBranding::class)
            ->set('data.brand_name', 'Yeni Marka')
            ->call('save')
            ->assertHasNoErrors();

        $this->post(route('admin.site-branding.logo.update'), [
            'logo' => UploadedFile::fake()->createWithContent(
                'logo.svg',
                '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 30"><path id="wordmark" d="M0 0h100v30H0z" fill="#106CFF"/><use href="#wordmark"/></svg>',
            )->mimeType('image/svg+xml'),
        ])->assertRedirect(route('filament.admin.pages.site-branding'))
            ->assertSessionHas('status', 'Logo başarıyla güncellendi.');

        $settings = SiteSetting::current();

        $this->assertSame('Yeni Marka', $settings->brand_name);
        $this->assertNotEmpty($settings->brand_logo_path);
        Storage::disk('public')->assertExists($settings->brand_logo_path);
        $this->assertStringContainsString('<use href="#wordmark"', Storage::disk('public')->get($settings->brand_logo_path));

        $this->get(route('filament.admin.pages.site-branding'))
            ->assertOk()
            ->assertSee($settings->brandLogoUrl())
            ->assertSee('Logo başarıyla güncellendi.');

        $this->get('/')
            ->assertOk()
            ->assertSee('Yeni Marka')
            ->assertSee($settings->brandLogoUrl())
            ->assertSee('logo-icon--custom')
            ->assertSee('grid-template-columns: auto;', false);
    }

    public function test_administrators_can_upload_favicon_used_on_the_site_and_admin_panel(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(SiteBranding::class)
            ->assertSee('Sekme simgesi')
            ->assertSee('Site logosu')
            ->assertSee('Logoyu düzenle')
            ->assertSee('brand-logo-upload-modal')
            ->assertSee('Yeni logo dosyası');

        $this->post(route('admin.site-branding.favicon.update'), [
            'favicon' => UploadedFile::fake()->image('favicon.png', 64, 64),
        ])->assertRedirect();

        $settings = SiteSetting::current();
        $this->assertNotEmpty($settings->favicon_path);
        $this->assertSame('image/png', $settings->faviconMimeType());
        Storage::disk('public')->assertExists($settings->favicon_path);

        $this->get('/')
            ->assertOk()
            ->assertSee('href="'.$settings->faviconUrl().'"', false)
            ->assertSee('type="image/png"', false);

        $this->get('/admin')
            ->assertOk()
            ->assertSee($settings->faviconUrl());
    }

    public function test_administrators_can_edit_footer_year_and_social_links(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        Livewire::test(SiteBranding::class)
            ->set('data.brand_name', 'Repost Plus')
            ->set('data.footer_year', 2030)
            ->set('data.instagram_url', 'https://instagram.com/repost')
            ->set('data.telegram_url', 'https://t.me/repost')
            ->set('data.threads_url', 'https://threads.net/@repost')
            ->call('save')
            ->assertHasNoErrors();

        $this->get('/')
            ->assertOk()
            ->assertSee('Repost Plus &copy;&nbsp;2030', false)
            ->assertSee('href="https://instagram.com/repost"', false)
            ->assertSee('href="https://t.me/repost"', false)
            ->assertSee('href="https://threads.net/@repost"', false);
    }

    public function test_svg_logo_sanitizer_removes_executable_and_external_content(): void
    {
        $svg = app(SvgSanitizer::class)->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10" onload="alert(1)"><script>alert(1)</script><path d="M0 0h10v10z" fill="#106CFF" onclick="alert(1)"/><image href="https://example.com/image.png"/></svg>',
        );

        $this->assertStringContainsString('<path', $svg);
        $this->assertStringNotContainsString('<script', $svg);
        $this->assertStringNotContainsString('<image', $svg);
        $this->assertStringNotContainsString('onload', $svg);
        $this->assertStringNotContainsString('onclick', $svg);
        $this->assertStringNotContainsString('https://example.com', $svg);
    }

    public function test_svg_sanitizer_preserves_safe_references_and_embedded_raster_images(): void
    {
        $svg = app(SvgSanitizer::class)->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg" xmlns:xlink="http://www.w3.org/1999/xlink" viewBox="0 0 10 10"><defs><g id="mark"><path d="M0 0h10v10z"/></g></defs><use xlink:href="#mark"/><image xlink:href="data:image/png;base64,iVBORw0KGgo="/><use href="https://example.com/logo.svg#mark"/></svg>',
        );

        $this->assertStringContainsString('<use href="#mark"', $svg);
        $this->assertStringContainsString('href="data:image/png;base64,iVBORw0KGgo="', $svg);
        $this->assertStringNotContainsString('https://example.com', $svg);
    }

    public function test_svg_sanitizer_rejects_logos_without_visible_artwork(): void
    {
        $this->expectException(\InvalidArgumentException::class);

        app(SvgSanitizer::class)->sanitize(
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10"><defs><clipPath id="clip"><path d="M0 0h10v10z"/></clipPath></defs><g/></svg>',
        );
    }

    public function test_administrators_can_upload_sanitized_svg_logos(): void
    {
        Storage::fake('public');
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $svgUpload = UploadedFile::fake()->createWithContent(
            'logo.svg',
            '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 10 10" onload="alert(1)"><script>alert(1)</script><path d="M0 0h10v10z"/></svg>',
        )->mimeType('image/svg+xml');

        $this->post(route('admin.site-branding.logo.update'), [
            'logo' => $svgUpload,
        ])->assertRedirect();

        $logoPath = SiteSetting::current()->brand_logo_path;
        Storage::disk('public')->assertExists($logoPath);

        $storedSvg = Storage::disk('public')->get($logoPath);
        $this->assertStringContainsString('<path', $storedSvg);
        $this->assertStringNotContainsString('<script', $storedSvg);
        $this->assertStringNotContainsString('onload', $storedSvg);

        $this->get('/')
            ->assertOk()
            ->assertSee(SiteSetting::current()->brandLogoUrl())
            ->assertSee('logo-icon--custom')
            ->assertSee('style="width: auto; height: 36px; max-width: 180px; object-fit: contain;"', false);

        $this->get('/')
            ->assertOk()
            ->assertSee(SiteSetting::current()->brandLogoUrl())
            ->assertSee('logo-icon--custom')
            ->assertSee('style="width: auto; height: 36px; max-width: 180px; object-fit: contain;"', false);
    }

    public function test_non_administrators_cannot_upload_a_site_logo(): void
    {
        Storage::fake('public');
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)
            ->post(route('admin.site-branding.logo.update'), [
                'logo' => UploadedFile::fake()->image('logo.png'),
            ])
            ->assertForbidden();

        $this->post(route('admin.site-branding.favicon.update'), [
            'favicon' => UploadedFile::fake()->image('favicon.png'),
        ])->assertForbidden();

        $this->assertDatabaseMissing('site_settings', ['id' => 1]);
        Storage::disk('public')->assertDirectoryEmpty('site-branding');
    }

    public function test_admin_grant_command_promotes_an_existing_account_after_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'promote@example.com',
            'is_admin' => false,
        ]);

        $this->artisan('admin:grant', ['email' => $user->email])
            ->expectsConfirmation('promote@example.com hesabına Filament yönetici erişimi verilsin mi?', 'yes')
            ->expectsOutputToContain('Yönetici erişimi verildi.')
            ->assertExitCode(0);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => true,
        ]);
    }

    public function test_admin_grant_command_does_not_promote_an_account_without_confirmation(): void
    {
        $user = User::factory()->create([
            'email' => 'not-promoted@example.com',
            'is_admin' => false,
        ]);

        $this->artisan('admin:grant', ['email' => $user->email])
            ->expectsConfirmation('not-promoted@example.com hesabına Filament yönetici erişimi verilsin mi?', 'no')
            ->expectsOutputToContain('İşlem iptal edildi.')
            ->assertExitCode(1);

        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'is_admin' => false,
        ]);
    }

    public function test_admin_grant_command_requires_an_existing_user(): void
    {
        $this->artisan('admin:grant', ['email' => 'missing@example.com'])
            ->expectsOutputToContain('Bu e-posta adresiyle kayıtlı bir kullanıcı bulunamadı.')
            ->assertExitCode(1);
    }

    public function test_administrators_can_open_post_and_channel_creation_forms(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->actingAs($admin);

        $this->get('/admin/posts/create')
            ->assertOk()
            ->assertSee('Başlık');

        $this->get('/admin/channels/create')
            ->assertOk()
            ->assertSee('Kanal adı');
    }

    public function test_admin_dashboard_shows_repost_management_overview(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $this->createPost(
            Channel::create([
                'name' => 'Panel kanalı',
                'slug' => 'panel-kanali',
                'is_active' => true,
            ]),
            [
                'title' => 'Panel taslağı',
                'slug' => 'panel-taslagi',
                'is_published' => false,
            ],
        );

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Repost genel durumu')
            ->assertSee('Üyeler')
            ->assertSee('Taslaklar')
            ->assertSee('Kanallar');
    }

    public function test_admins_can_open_comment_moderation_pages(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $channel = Channel::create([
            'name' => 'Yorum kanalı',
            'slug' => 'yorum-kanali',
            'is_active' => true,
        ]);
        $post = $this->createPost($channel, [
            'title' => 'Yorum yazısı',
            'slug' => 'yorum-yazisi',
        ]);
        $commenter = User::factory()->create();
        Comment::create([
            'post_id' => $post->id,
            'user_id' => $commenter->id,
            'body' => 'Yönetilecek yorum.',
        ]);

        $this->actingAs($admin)
            ->get('/admin/comments')
            ->assertOk()
            ->assertSee('Yorumlar');

        $this->get('/admin/comments/create')
            ->assertOk()
            ->assertSee('Yorum')
            ->assertSee('Yazı')
            ->assertSee('Yazar');
    }

    private function createPost(Channel $channel, array $attributes = []): Post
    {
        return Post::create([
            'channel_id' => $channel->id,
            'title' => 'Örnek yazı',
            'slug' => 'ornek-yazi-'.fake()->unique()->numerify('###'),
            'excerpt' => 'Kısa açıklama',
            'body' => 'Yazı metni',
            'tags' => ['örnek'],
            'is_published' => true,
            'published_at' => now()->subMinute(),
            ...$attributes,
        ]);
    }
}
