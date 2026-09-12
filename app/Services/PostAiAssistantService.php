<?php

namespace App\Services;

class PostAiAssistantService
{
    public function assist(string $title, string $content): array
    {
        throw new \RuntimeException('Yapay zeka yazı asistanı devre dışı.');
    }
}
