<?php

namespace App\Filament\Pages;

use App\Models\ReputationSetting;
use App\Models\User;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Pages\Page;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ReputationManagement extends Page
{
    protected static string|\UnitEnum|null $navigationGroup = 'Kullanıcı Yönetimi';
    protected static ?string $navigationLabel = 'İtibar ve ödüller';
    protected static ?string $title = 'İtibar, puan ve ödül yönetimi';
    protected static string|\BackedEnum|null $navigationIcon = Heroicon::OutlinedTrophy;
    protected static ?int $navigationSort = 8;
    protected string $view = 'filament.pages.reputation-management';

    public ?array $data = [];

    public function mount(): void
    {
        $settings = ReputationSetting::current();
        $this->form->fill($settings->toArray());
    }

    public function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make('Sistemin genel ayarları')->schema([
                Toggle::make('enabled')->label('Puan sistemini etkinleştir')->default(true),
                Toggle::make('exclude_company_accounts')->label('Şirket hesaplarını puanlamadan muaf tut')->default(true),
            ])->columns(2),
            Section::make('Puanlama kuralları')->description('Her etkinlik için verilecek puanı belirleyin.')->schema([
                TextInput::make('post_points')->label('Yayınlanan yazı')->numeric()->minValue(0)->required(),
                TextInput::make('comment_points')->label('Yapılan yorum')->numeric()->minValue(0)->required(),
                TextInput::make('post_like_points')->label('Yazı beğenisi')->numeric()->minValue(0)->required(),
                TextInput::make('comment_like_points')->label('Yorum beğenisi')->numeric()->minValue(0)->required(),
            ])->columns(2),
            Section::make('Özel ödül rozetleri')->description('Puan eşiğine ulaşan kullanıcılara otomatik tanımlanacak rozetleri ekleyin. En yüksek puan eşiği karşılanan rozet profilde gösterilir.')->schema([
                Repeater::make('reward_badges')->label('Ödül rozetleri')->schema([
                    TextInput::make('name')->label('Rozet adı')->required()->maxLength(80),
                    TextInput::make('required_points')->label('Gerekli puan')->numeric()->minValue(0)->required(),
                    FileUpload::make('image')->label('Rozet görseli')->image()->disk('public')->directory('reputation-badges')->visibility('public')->imageEditor()->maxSize(2048)->helperText('Rozet için PNG, JPG, WEBP veya SVG olmayan bir görsel yükleyin. En fazla 2 MB.'),
                    TextInput::make('icon')->label('İkon adı (Lucide)')->placeholder('trophy')->maxLength(60),
                    Textarea::make('description')->label('Açıklama')->rows(2)->maxLength(250)->columnSpanFull(),
                ])->columns(2)->defaultItems(0)->addActionLabel('Yeni ödül rozeti ekle')->reorderable()->live()->columnSpanFull(),
            ]),
            Section::make('Görevler')->description('Kullanıcıların tamamlayabileceği görevleri oluşturun. Görevler herkese açık listelenir; tamamlayanlara puan ve seçtiğiniz rozet otomatik verilir.')->schema([
                Repeater::make('tasks')->label('Topluluk görevleri')->schema([
                    Hidden::make('key'),
                    TextInput::make('title')->label('Görev adı')->required()->maxLength(120),
                    Select::make('activity')->label('Yapılacak etkinlik')->options([
                        'comments' => 'Yorum yap',
                        'posts' => 'Yazı yayınla',
                        'event' => 'Etkinlik / yönetici onaylı görev',
                    ])->required()->default('comments'),
                    DateTimePicker::make('starts_at')->label('Başlangıç')->required()->default(now()),
                    DateTimePicker::make('ends_at')->label('Bitiş (isteğe bağlı)')->after('starts_at')->nullable(),
                    Select::make('approved_users')->label('Etkinliği / görevi tamamladığı onaylanan üyeler')->multiple()->searchable()
                        ->getSearchResultsUsing(fn (string $search): array => User::query()->where('username', 'like', '%'.$search.'%')->limit(30)->pluck('username', 'id')->all())
                        ->getOptionLabelsUsing(fn (array $values): array => User::query()->whereIn('id', $values)->pluck('username', 'id')->all())
                        ->helperText('Etkinlik türünde yalnızca bu üyeler ödül alır. Gerekli adet 1 olarak uygulanır.')->columnSpanFull(),
                    TextInput::make('target_count')->label('Gerekli adet')->numeric()->minValue(1)->required()->default(3),
                    TextInput::make('reward_points')->label('Görev puanı')->numeric()->minValue(0)->required()->default(10),
                    Select::make('reward_badge')->label('Görev tamamlanınca verilecek rozet')
                        ->options(fn (): array => collect($this->data['reward_badges'] ?? [])
                            ->filter(fn (array $badge): bool => filled($badge['name'] ?? null))
                            ->mapWithKeys(fn (array $badge): array => [$badge['name'] => $badge['name']])
                            ->all())
                        ->searchable()->placeholder('Rozet seçme (isteğe bağlı)')->nullable(),
                    Textarea::make('description')->label('Görev açıklaması')->rows(2)->maxLength(250)->columnSpanFull(),
                    Toggle::make('active')->label('Görev yayında')->default(true),
                ])->columns(2)->defaultItems(0)->addActionLabel('Yeni görev ekle')->reorderable()->columnSpanFull(),
            ]),
        ])->statePath('data');
    }

    public function save(): void
    {
        $data = $this->form->getState();
        ReputationSetting::current()->update([
            'enabled' => (bool) ($data['enabled'] ?? false),
            'exclude_company_accounts' => (bool) ($data['exclude_company_accounts'] ?? true),
            'post_points' => (int) $data['post_points'],
            'comment_points' => (int) $data['comment_points'],
            'post_like_points' => (int) $data['post_like_points'],
            'comment_like_points' => (int) $data['comment_like_points'],
            'reward_badges' => array_values($data['reward_badges'] ?? []),
            'tasks' => collect($data['tasks'] ?? [])->map(function (array $task): array {
                $task['key'] = filled($task['key'] ?? null) ? $task['key'] : (string) Str::uuid();
                $task['starts_at'] = filled($task['starts_at'] ?? null) ? $task['starts_at'] : now()->toDateTimeString();
                $task['target_count'] = ($task['activity'] ?? '') === 'event' ? 1 : max(1, (int) ($task['target_count'] ?? 1));
                $task['reward_points'] = max(0, (int) ($task['reward_points'] ?? 0));
                return $task;
            })->values()->all(),
        ]);

        $this->form->fill(ReputationSetting::current()->toArray());
        $approvedIds = collect($data['tasks'] ?? [])->where('activity', 'event')->pluck('approved_users')->flatten()->unique();
        User::query()->whereIn('id', $approvedIds)->each(function (User $user): void {
            app(\App\Services\CommunityTaskService::class)->completeAvailableTasks($user);
        });

        Notification::make()->title('İtibar ve ödül ayarları kaydedildi.')->success()->send();
    }
}
