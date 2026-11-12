<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class NotificationSystemSetting extends Model
{
    protected $fillable = ['settings'];

    protected function casts(): array
    {
        return ['settings' => 'array'];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            ['settings' => [
                'enabled' => true,
                'stream_enabled' => true,
                'email_enabled' => true,
                'push_enabled' => true,
                'categories' => [
                    'replies' => true,
                    'posts' => true,
                    'announcements' => true,
                    'followers' => true,
                    'messages' => true,
                    'mentions' => true,
                    'security' => true,
                ],
            ]],
        );
    }

    public function enabled(string $key, bool $default = true): bool
    {
        return (bool) data_get($this->settings ?? [], $key, $default);
    }
}
