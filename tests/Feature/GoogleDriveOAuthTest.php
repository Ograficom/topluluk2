<?php

namespace Tests\Feature;

use Tests\TestCase;

class GoogleDriveOAuthTest extends TestCase
{
    public function test_google_oauth_callback_rejects_a_mismatched_state(): void
    {
        $response = $this
            ->withSession(['google_drive_oauth_state' => 'expected-state'])
            ->get('/auth/google-drive/callback?code=sample-code&state=attacker-state');

        $response->assertStatus(419);
    }

    public function test_google_oauth_callback_rejects_a_missing_state(): void
    {
        $response = $this->get('/auth/google-drive/callback?code=sample-code');

        $response->assertStatus(419);
    }
}
