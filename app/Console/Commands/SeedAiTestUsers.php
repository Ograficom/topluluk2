<?php

namespace App\Console\Commands;

use App\Support\Testing\AiTestUserProfiles;
use Illuminate\Console\Command;

class SeedAiTestUsers extends Command
{
    protected $signature = 'testing:seed-ai-users';

    protected $description = 'Create repeatable AI test personas for local and testing environments';

    public function handle(): int
    {
        if (! app()->environment(['local', 'testing'])) {
            $this->error('AI test users can only be seeded in the local or testing environment.');

            return self::FAILURE;
        }

        $users = AiTestUserProfiles::seed();

        $this->table(
            ['Persona', 'E-posta', 'Rol'],
            $users->map(fn ($user) => [$user->ai_persona, $user->email, $user->role])->all(),
        );

        $this->info("{$users->count()} AI test kullanicisi hazir.");

        return self::SUCCESS;
    }
}
