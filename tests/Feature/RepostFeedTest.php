<?php

namespace Tests\Feature;

use App\Models\Channel;
use App\Models\Comment;
use App\Models\Post;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
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

    public function test_invalid_post_cover_is_hidden_and_uploaded_cover_uses_public_storage_url(): void
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
            ->assertSee(Storage::disk('public')->url('posts/valid-cover.png'), false);
        $this->assertStringNotContainsString('src="'.asset('merhaba').'"', $response->getContent());
        $this->assertSame(
            Storage::disk('public')->url('posts/valid-cover.png'),
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
        $channel = Channel::create([
            'name' => 'Yolculuk',
            'slug' => 'yolculuk',
            'is_active' => true,
        ]);
        $published = $this->createPost($channel, [
            'title' => 'Yol hikâyesi',
            'slug' => 'yol-hikayesi',
            'body' => 'Yazının tam metni.',
        ]);
        $draft = $this->createPost($channel, [
            'title' => 'Gizli taslak',
            'slug' => 'gizli-taslak',
            'is_published' => false,
        ]);

        $this->get('/posts/yol-hikayesi')
            ->assertOk()
            ->assertSee('Yazının tam metni.');

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

        $this->post('/posts/'.$post->slug.'/comments', ['body' => 'Güzel paylaşım.'])
            ->assertRedirect(route('posts.show', $post).'#comments');

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
            ->assertDontSee('Gizli kanal');

        $this->get('/channels/tasarim')
            ->assertOk()
            ->assertSee($published->title)
            ->assertDontSee('Taslak yazı');

        $this->get('/channels/gizli-kanal')->assertNotFound();
    }

    public function test_only_administrators_can_open_the_filament_panel(): void
    {
        $user = User::factory()->create(['is_admin' => false]);

        $this->actingAs($user)->get('/admin')->assertForbidden();
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
