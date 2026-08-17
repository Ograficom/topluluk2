<?php

namespace Tests\Unit;

use App\Models\User;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;

class PrivacyVisibilityTest extends TestCase
{
    #[Test]
    public function public_visibility_is_available_to_guests(): void
    {
        $owner = new User(['posts_visibility' => 'public']);

        $this->assertTrue($owner->allowsVisibility(null, 'posts_visibility'));
    }

    #[Test]
    public function private_visibility_is_available_only_to_the_owner(): void
    {
        $owner = new User(['posts_visibility' => 'private']);
        $owner->id = 10;

        $viewer = new User();
        $viewer->id = 20;

        $this->assertFalse($owner->allowsVisibility($viewer, 'posts_visibility'));
        $this->assertTrue($owner->allowsVisibility($owner, 'posts_visibility'));
    }

    #[Test]
    public function unknown_privacy_attributes_fail_closed(): void
    {
        $owner = new User();
        $owner->id = 10;

        $this->assertFalse($owner->allowsVisibility(null, 'unknown_visibility'));
    }
}
