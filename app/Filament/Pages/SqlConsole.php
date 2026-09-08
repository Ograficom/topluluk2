<?php

namespace App\Filament\Pages;

use App\Services\DatabaseConsoleService;
use Filament\Notifications\Notification;
use Filament\Pages\Page;

class SqlConsole extends Page
{
    protected static string | \BackedEnum | null $navigationIcon = 'heroicon-o-circle-stack';

    protected static string | \UnitEnum | null $navigationGroup = 'Ayarlar';

    protected static ?string $navigationLabel = 'SQL Konsolu';

    protected static ?int $navigationSort = 999;

    protected string $view = 'filament.pages.sql-console';

    public string $sql = 'SHOW TABLES;';

    public bool $confirmWrite = false;

    /** @var array<int, string> */
    public array $columns = [];

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public ?int $affected = null;

    public ?float $elapsedMs = null;

    public bool $truncated = false;

    public ?string $errorMessage = null;

    public static function canAccess(): bool
    {
        return auth()->user()?->isAdmin() ?? false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return static::canAccess();
    }

    public function mount(): void
    {
        abort_unless(static::canAccess(), 403);
    }

    public function getTitle(): string
    {
        return 'SQL Konsolu';
    }

    public function runQuery(): void
    {
        abort_unless(static::canAccess(), 403);

        $this->resetResults();

        try {
            $result = app(DatabaseConsoleService::class)->execute(
                $this->sql,
                $this->confirmWrite,
            );

            $this->columns = $result['columns'];
            $this->rows = $result['rows'];
            $this->affected = $result['affected'];
            $this->elapsedMs = $result['elapsed_ms'];
            $this->truncated = $result['truncated'];

            if (! $result['read_only']) {
                $this->confirmWrite = false;
            }

            Notification::make()
                ->title($result['read_only'] ? 'Sorgu tamamlandi' : 'SQL degisikligi uygulandi')
                ->body($result['read_only']
                    ? count($result['rows']) . ' satir getirildi.'
                    : ((int) $result['affected']) . ' satir etkilendi.')
                ->success()
                ->send();
        } catch (\Throwable $e) {
            report($e);
            $this->errorMessage = $e->getMessage();

            Notification::make()
                ->title('SQL sorgusu calistirilamadi')
                ->body($e->getMessage())
                ->danger()
                ->persistent()
                ->send();
        }
    }

    public function useExample(string $type): void
    {
        $this->sql = match ($type) {
            'tables' => 'SHOW TABLES;',
            'users' => 'SELECT id, name, username, email, role, created_at FROM users ORDER BY id DESC LIMIT 50;',
            'posts' => 'SELECT id, title, slug, is_published, published_at, created_at FROM posts ORDER BY id DESC LIMIT 50;',
            default => $this->sql,
        };

        $this->confirmWrite = false;
        $this->resetResults();
    }

    public function clearConsole(): void
    {
        $this->sql = '';
        $this->confirmWrite = false;
        $this->resetResults();
    }

    private function resetResults(): void
    {
        $this->columns = [];
        $this->rows = [];
        $this->affected = null;
        $this->elapsedMs = null;
        $this->truncated = false;
        $this->errorMessage = null;
    }
}
