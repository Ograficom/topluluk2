<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MailSetting extends Model
{
    protected $fillable = [
        'enabled',
        'mailer',
        'scheme',
        'host',
        'port',
        'username',
        'password',
        'from_address',
        'from_name',
    ];

    protected function casts(): array
    {
        return [
            'enabled' => 'boolean',
            'port' => 'integer',
            'password' => 'encrypted',
        ];
    }

    public static function current(): self
    {
        return static::query()->firstOrCreate(
            ['id' => 1],
            [
                'enabled' => false,
                'mailer' => 'smtp',
                'scheme' => 'smtps',
                'host' => 'smtp.hostinger.com',
                'port' => 465,
                'from_name' => 'OgraFi',
            ],
        );
    }
}
