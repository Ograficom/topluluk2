<?php

namespace Tests\Unit;

use App\Services\GoogleDrive\GoogleDriveOAuthState;
use PHPUnit\Framework\TestCase;

class GoogleDriveOAuthStateTest extends TestCase
{
    public function test_issued_state_is_64_hex_characters(): void
    {
        $state = GoogleDriveOAuthState::issue();

        $this->assertMatchesRegularExpression('/^[a-f0-9]{64}$/', $state);
    }

    public function test_only_the_exact_non_empty_state_is_accepted(): void
    {
        $this->assertTrue(GoogleDriveOAuthState::matches('expected-state', 'expected-state'));
        $this->assertFalse(GoogleDriveOAuthState::matches('expected-state', 'attacker-state'));
        $this->assertFalse(GoogleDriveOAuthState::matches('expected-state', null));
        $this->assertFalse(GoogleDriveOAuthState::matches(null, 'received-state'));
        $this->assertFalse(GoogleDriveOAuthState::matches('', ''));
    }
}
