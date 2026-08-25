<?php

namespace Tests\Feature;

use App\Models\Report;
use App\Models\User;
use App\Services\AiLiveModerationService;
use App\Services\OllamaService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Mockery;
use Tests\TestCase;

class AiLiveModerationServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_review_only_reports_for_flagged_profiles(): void
    {
        User::factory()->create([
            'name' => 'Riskli Profil',
            'bio' => 'Supheli bir profil metni.',
        ]);

        $ollama = Mockery::mock(OllamaService::class);
        $ollama->shouldReceive('chatStructured')
            ->twice()
            ->andReturn([
                'flag' => true,
                'topic' => 'Dolandiricilik',
                'risk_score' => 85,
                'reason' => 'Inceleme gerektiren dolandiricilik sinyali.',
            ]);

        $result = (new AiLiveModerationService($ollama))->scan(1);

        $this->assertSame(['scanned' => 2, 'flagged' => 2, 'errors' => 0], $result);
        $this->assertSame(2, Report::query()->where('status', 'pending')->count());
        $this->assertSame(2, User::query()->whereIn('ai_persona', ['spam-moderator', 'safety-moderator'])->count());
        $this->assertStringContainsString('[AI-MOD:', (string) Report::query()->first()->description);
    }
}
