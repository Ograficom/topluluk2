<?php

namespace Database\Seeders;

use App\Models\Faq;
use Illuminate\Database\Seeder;

class FaqSeeder extends Seeder
{
    /**
     * Seed the application's database with default FAQ items.
     */
    public function run(): void
    {
        $items = [
            [
                'question' => 'Alma nedir?',
                'answer' => 'Alma, topluluklar&#305;n payla&#351;&#305;m yapabildi&#287;i ve yaz&#305;lar&#305;n yorumlarla tart&#305;&#351;&#305;labildi&#287;i bir platformdur.',
                'sort_order' => 1,
            ],
            [
                'question' => 'Hesap nasil olustururum?',
                'answer' => 'Kay&#305;t sayfas&#305;ndan yeni bir hesap olu&#351;turabilir veya desteklenen sosyal hesaplarla giri&#351; yapabilirsin.',
                'sort_order' => 2,
            ],
            [
                'question' => 'Yazi nasil paylasirim?',
                'answer' => 'Giri&#351; yapt&#305;ktan sonra Write butonunu kullanarak yeni bir yaz&#305; olu&#351;turabilirsin.',
                'sort_order' => 3,
            ],
            [
                'question' => 'Yorum nasil eklerim?',
                'answer' => 'Bir yaz&#305;n&#305;n alt&#305;ndaki yorum b&#246;l&#252;m&#252;nden yorum ekleyebilirsin.',
                'sort_order' => 4,
            ],
            [
                'question' => 'Yer imlerine nasil kaydederim?',
                'answer' => 'Yaz&#305; kart&#305;ndaki yer imi ikonuna t&#305;klayarak kaydedebilirsin.',
                'sort_order' => 5,
            ],
            [
                'question' => 'Bildirimleri nereden gorurum?',
                'answer' => 'Bildirimler men&#252;s&#252;nden g&#252;ncel etkile&#351;imleri takip edebilirsin.',
                'sort_order' => 6,
            ],
        ];

        foreach ($items as $item) {
            Faq::query()->updateOrCreate(
                ['question' => $item['question']],
                [
                    'answer' => $item['answer'],
                    'sort_order' => $item['sort_order'],
                    'is_active' => true,
                ]
            );
        }
    }
}
