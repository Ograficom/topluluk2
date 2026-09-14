<?php

declare(strict_types=1);

namespace App\Filament\Resources\BannedPostWordResource\Pages;

use App\Filament\Resources\BannedPostWordResource;
use Filament\Resources\Pages\CreateRecord;

class CreateBannedPostWord extends CreateRecord
{
    protected static string $resource = BannedPostWordResource::class;
}
