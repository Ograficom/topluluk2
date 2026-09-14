<?php

declare(strict_types=1);

namespace App\Filament\Resources\BannedPostWordResource\Pages;

use App\Filament\Resources\BannedPostWordResource;
use Filament\Resources\Pages\EditRecord;

class EditBannedPostWord extends EditRecord
{
    protected static string $resource = BannedPostWordResource::class;
}
