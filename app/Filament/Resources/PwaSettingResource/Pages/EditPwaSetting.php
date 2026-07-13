<?php

namespace App\Filament\Resources\PwaSettingResource\Pages;

use App\Filament\Resources\PwaSettingResource;
use App\Services\PwaManifestService;
use Illuminate\Support\Facades\File;
use Filament\Resources\Pages\EditRecord;

class EditPwaSetting extends EditRecord
{
    protected static string $resource = PwaSettingResource::class;

    protected function afterSave(): void
    {
        $record = $this->getRecord();
        if (!$record) {
            return;
        }

        $manifestPath = public_path('manifest.json');
        if (!$record->is_enabled) {
            if (File::exists($manifestPath)) {
                File::delete($manifestPath);
            }
            return;
        }

        $manifest = app(PwaManifestService::class)->buildManifest($record);
        File::put($manifestPath, json_encode($manifest, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
    }
}
