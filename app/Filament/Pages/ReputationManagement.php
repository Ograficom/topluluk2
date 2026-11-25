<?php

namespace App\Filament\Pages;

use App\Models\ReputationSetting;
use App\Models\User;
use App\Models\TaskParticipation;
use App\Models\TaskCompletion;
use App\Services\CommunityTaskService;
use Illuminate\Support\Facades\DB;
use Livewire\WithPagination;
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
use Filament\Schemas\Components\Tabs;
use Filament\Schemas\Components\Tabs\Tab;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Illuminate\Support\Str;

class ReputationManagement extends Page
{
    use WithPagination;

    public string $participationFilter = 'pending';
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
            Tabs::make('İtibar yönetimi')
                ->tabs([
                    Tab::make('Görevler')->icon(Heroicon::OutlinedClipboardDocumentList)->schema([
                        Section::make('Görevler')->description('Kullanıcıların tamamlayabileceği görevleri oluşturun. Görevler herkese açık listelenir; tamamlayanlara puan ve seçtiğiniz rozet otomatik verilir.')->schema([
                            Repeater::make('tasks')->label('Topluluk görevleri')->schema([
                                Hidden::make('key'),
                                TextInput::make('title')->label('Görev adı')->required()->maxLength(120),
                                Select::make('activity')->label('Yapılacak etkinlik')->options(\App\Services\CommunityTaskService::ACTIVITIES)->required()->default('comments')->live(),
                                TextInput::make('action_url')->label('Sosyal medya hesabı / etkinlik bağlantısı')->url()->maxLength(2048)->nullable()
                                    ->rules(['nullable', 'url:http,https'])
                                    ->helperText('Sosyal takip harici platformda doğrulanır; onaylanan üyeleri aşağıdan seçin.'),
                                DateTimePicker::make('starts_at')->label('Başlangıç')->required()->default(now()),
                                DateTimePicker::make('ends_at')->label('Bitiş (isteğe bağlı)')->after('starts_at')->nullable(),
                                Select::make('approved_users')->label('Etkinliği / görevi tamamladığı onaylanan üyeler')->multiple()->searchable()
                                    ->getSearchResultsUsing(fn (string $search): array => User::query()->where('username', 'like', '%'.$search.'%')->limit(30)->pluck('username', 'id')->all())
                                    ->getOptionLabelsUsing(fn (array $values): array => User::query()->whereIn('id', $values)->pluck('username', 'id')->all())
                                    ->visible(fn (\Filament\Schemas\Components\Utilities\Get $get): bool => in_array($get('activity'), ['event', 'social_follow'], true))
                                    ->helperText('Etkinlik türünde yalnızca bu üyeler ödül alır. Gerekli adet 1 olarak uygulanır.')->columnSpanFull(),
                                TextInput::make('target_count')->label('Gerekli adet')->numeric()->minValue(1)->required()->default(3)->helperText('Takipte farklı üye sayısı, girişte ardışık gün sayısıdır. Profil, güvenlik ve onay görevlerinde hedef 1 olur.'),
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
                    ]),
                    Tab::make('Rozetler')->icon(Heroicon::OutlinedTrophy)->schema([
                        Section::make('Özel ödül rozetleri')->description('Puan eşiğine ulaşan kullanıcılara otomatik tanımlanacak rozetleri ekleyin. En yüksek puan eşiği karşılanan rozet profilde gösterilir.')->schema([
                            Repeater::make('reward_badges')->label('Ödül rozetleri')->schema([
                                TextInput::make('name')->label('Rozet adı')->required()->maxLength(80),
                                TextInput::make('required_points')->label('Gerekli puan')->numeric()->minValue(0)->required(),
                                FileUpload::make('image')->label('Rozet görseli')->image()->disk('public')->directory('reputation-badges')->visibility('public')->imageEditor()->maxSize(2048)->helperText('Rozet için PNG, JPG, WEBP veya SVG olmayan bir görsel yükleyin. En fazla 2 MB.'),
                                TextInput::make('icon')->label('İkon adı (Lucide)')->placeholder('trophy')->maxLength(60),
                                Textarea::make('description')->label('Açıklama')->rows(2)->maxLength(250)->columnSpanFull(),
                            ])->columns(2)->defaultItems(0)->addActionLabel('Yeni ödül rozeti ekle')->reorderable()->live()->columnSpanFull(),
                        ]),
                    ]),
                    Tab::make('Sistem ayarı')->icon(Heroicon::OutlinedCog6Tooth)->schema([
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
                    ]),
                ])->columnSpanFull(),
        ])->statePath('data');
    }

    public function updatedParticipationFilter(): void
    {
        $this->resetPage('taskParticipantsPage');
    }

    public function getParticipants()
    {
        abort_unless(auth()->user()?->is_admin, 403);
        return TaskParticipation::query()->with('user')
            ->when(in_array($this->participationFilter, ['pending', 'approved', 'rejected'], true),
                fn ($query) => $query->where('status', $this->participationFilter))
            ->latest()->orderByDesc('id')->paginate(20, ['*'], 'taskParticipantsPage');
    }

    public function reviewParticipation(int $id, bool $approve): void
    {
        abort_unless(auth()->user()?->is_admin, 403);
        try {
            $review = DB::transaction(function () use ($id, $approve): ?array {
                $settings = ReputationSetting::query()->whereKey(ReputationSetting::current()->id)->lockForUpdate()->firstOrFail();
                $participation = TaskParticipation::query()->with('user')->lockForUpdate()->findOrFail($id);
                if ($participation->status !== 'pending') return null;
                $tasks = $settings->tasks ?? [];
                $index = collect($tasks)->search(fn (array $item): bool => ($item['key'] ?? '') === $participation->task_key);
                if (TaskCompletion::query()->where('user_id', $participation->user_id)->where('task_key', $participation->task_key)->exists()) {
                    $participation->update(['status' => 'approved']);
                    return ['status' => 'approved', 'key' => $participation->task_key, 'users' => $index !== false ? ($tasks[$index]['approved_users'] ?? []) : []];
                }
                if (! $approve) {
                    $participation->update(['status' => 'rejected']);
                    return ['status' => 'rejected'];
                }
                if ($index === false || ! ($tasks[$index]['active'] ?? true) || ! in_array($tasks[$index]['activity'] ?? '', ['event', 'social_follow'], true)) {
                    throw new \DomainException('Görev artık aktif değil.');
                }
                $task = $tasks[$index];
                if (! $settings->enabled || ($settings->exclude_company_accounts && $participation->user->is_company)
                    || (filled($task['starts_at'] ?? null) && now()->lt($task['starts_at']))
                    || (filled($task['ends_at'] ?? null) && now()->gt($task['ends_at']))) {
                    throw new \DomainException('Görev şu anda ödül vermeye uygun değil.');
                }
                $users = array_values(array_unique(array_map('strval', [...($task['approved_users'] ?? []), $participation->user_id])));
                $tasks[$index]['approved_users'] = $users;
                $settings->update(['tasks' => $tasks]);
                app(CommunityTaskService::class)->completeAvailableTasks($participation->user);
                if (! TaskCompletion::query()->where('user_id', $participation->user_id)->where('task_key', $participation->task_key)->exists()) {
                    throw new \DomainException('Ödül verilemedi. Görev ayarlarını kontrol edin.');
                }
                $participation->update(['status' => 'approved']);
                return ['status' => 'approved', 'key' => $participation->task_key, 'users' => $users];
            });
        } catch (\DomainException $exception) {
            Notification::make()->title($exception->getMessage())->danger()->send();
            return;
        }
        if ($review === null) return;
        if ($review['status'] === 'approved') {
            foreach ($this->data['tasks'] ?? [] as $key => $task) {
                if (($task['key'] ?? '') === $review['key']) {
                    $this->data['tasks'][$key]['approved_users'] = array_values(array_unique(array_map('strval', [...($task['approved_users'] ?? []), ...$review['users']])));
                }
            }
        }
        Notification::make()->title($review['status'] === 'approved' ? 'Başvuru onaylandı, ödül verildi.' : 'Başvuru reddedildi.')->success()->send();
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
                $task['target_count'] = in_array($task['activity'] ?? '', ['event', 'social_follow', 'registration', 'profile_complete', 'two_factor', 'avatar_added', 'avatar_changed'], true) ? 1 : max(1, (int) ($task['target_count'] ?? 1));
                $task['reward_points'] = max(0, (int) ($task['reward_points'] ?? 0));
                return $task;
            })->values()->all(),
        ]);

        $this->form->fill(ReputationSetting::current()->toArray());
        $approvedIds = collect($data['tasks'] ?? [])->whereIn('activity', ['event', 'social_follow'])->pluck('approved_users')->flatten()->unique();
        User::query()->whereIn('id', $approvedIds)->each(function (User $user): void {
            app(\App\Services\CommunityTaskService::class)->completeAvailableTasks($user);
            $completedKeys = TaskCompletion::query()->where('user_id', $user->id)->pluck('task_key');
            TaskParticipation::query()->where('user_id', $user->id)->whereIn('task_key', $completedKeys)->update(['status' => 'approved']);
        });

        Notification::make()->title('İtibar ve ödül ayarları kaydedildi.')->success()->send();
    }
}
