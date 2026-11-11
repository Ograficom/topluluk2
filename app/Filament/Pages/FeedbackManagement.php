<?php

namespace App\\Filament\\Pages;

use Filament\\Pages\\Page;

class FeedbackManagement extends Page
{
    protected static ?string $slug = 'feedback-management';

    protected static ?string $navigationLabel = 'Geri Bildirim Yönetimi';

    protected static ?string $title = 'Geri Bildirim Yönetimi';

    protected static bool $shouldRegisterNavigation = false;

    protected string $view = 'filament.pages.feedback-management';

    public function getHeading(): ?string
    {
        return null;
    }
}
