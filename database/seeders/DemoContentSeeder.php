<?php

namespace Database\Seeders;

use App\Models\Channel;
use App\Models\Post;
use App\Models\User;
use Illuminate\Database\Seeder;

class DemoContentSeeder extends Seeder
{
    public function run(): void
    {
        $channels = [
            'yolculuk' => Channel::query()->firstOrCreate(
                ['slug' => 'yolculuk'],
                ['name' => 'Yolculuk', 'description' => 'Yeni yerler, rotalar ve yol hikâyeleri.', 'is_active' => true],
            ),
            'teknoloji' => Channel::query()->firstOrCreate(
                ['slug' => 'teknoloji'],
                ['name' => 'Teknoloji', 'description' => 'Teknoloji dünyasından fikirler ve gelişmeler.', 'is_active' => true],
            ),
            'tasarim' => Channel::query()->firstOrCreate(
                ['slug' => 'tasarim'],
                ['name' => 'Tasarım', 'description' => 'Görsel kültür, yaratıcılık ve tasarım.', 'is_active' => true],
            ),
        ];
        $author = User::query()->where('is_admin', true)->first();

        $posts = [
            [
                'channel' => 'yolculuk',
                'title' => 'Yola çıkmadan önce: daha keyifli bir seyahat için küçük notlar',
                'slug' => 'keyifli-seyahat-icin-notlar',
                'excerpt' => 'İyi bir rota, hafif bir çanta ve biraz esneklik; güzel bir yolculuk için çoğu zaman yeterli.',
                'body' => "Her yolculuk, planın dışına çıkmaya da yer bıraktığında güzelleşiyor.\n\nYola çıkmadan önce görmek istediğiniz birkaç yeri belirleyin; geri kalanını keşfe açık bırakın. Yanınıza gerçekten ihtiyacınız olanları alın ve yol boyunca yerel insanlardan öneri istemekten çekinmeyin.\n\nBazen en güzel durak, haritada işaretli olmayan küçük bir sokak oluyor.",
                'cover_image' => 'assets/img/demo/demo-horisontal.png',
                'tags' => ['seyahat', 'gezi', 'notlar'],
                'days_ago' => 1,
            ],
            [
                'channel' => 'teknoloji',
                'title' => 'Daha odaklı çalışmak için sade bir dijital düzen',
                'slug' => 'sade-dijital-duzen',
                'excerpt' => 'Bildirimleri azaltmak ve kullandığınız araçları sadeleştirmek, dikkatinizi geri kazanmanıza yardımcı olabilir.',
                'body' => "Dijital araçlar işimizi kolaylaştırmak için var; dikkatimizi sürekli bölmek için değil.\n\nGün içinde gerçekten takip etmeniz gereken bildirimleri açık bırakın. Kullandığınız uygulamaları tek bir yerde toplayın ve odaklanma zamanlarınızı takvimde koruyun.\n\nKüçük bir düzenleme bile daha sakin ve verimli bir günün başlangıcı olabilir.",
                'cover_image' => 'assets/img/demo/demo-vertical.png',
                'tags' => ['teknoloji', 'üretkenlik', 'odak'],
                'days_ago' => 2,
            ],
            [
                'channel' => 'tasarim',
                'title' => 'Günlük hayatta iyi tasarımı fark etmek',
                'slug' => 'gunluk-hayatta-iyi-tasarim',
                'excerpt' => 'Bazen iyi tasarım, kendini göstermeden işini yapan küçük bir ayrıntıdır.',
                'body' => "İyi tasarlanmış bir nesne ya da ekran, çoğu zaman nasıl çalıştığını uzun uzun anlatmak zorunda kalmaz.\n\nAnlaşılır bir yönlendirme, rahatça okunabilen bir metin veya elinize tam oturan bir fincan; hepsi aynı düşüncenin farklı örnekleri. Günlük hayatta bu ayrıntılara dikkat etmek, tasarımın yalnızca görünüşten ibaret olmadığını hatırlatıyor.",
                'cover_image' => null,
                'tags' => ['tasarım', 'yaratıcılık'],
                'days_ago' => 3,
            ],
        ];

        foreach ($posts as $attributes) {
            $channel = $channels[$attributes['channel']];
            $daysAgo = $attributes['days_ago'];
            unset($attributes['channel'], $attributes['days_ago']);

            Post::query()->firstOrCreate(
                ['slug' => $attributes['slug']],
                [
                    ...$attributes,
                    'user_id' => $author?->id,
                    'channel_id' => $channel->id,
                    'is_published' => true,
                    'published_at' => now()->subDays($daysAgo),
                ],
            );
        }
    }
}
