<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallationRoutesTest extends TestCase
{
    private bool $createdInstallLock = false;

    protected function setUp(): void
    {
        parent::setUp();

        $path = InstallState::lockPath();

        if (!is_file($path)) {
            if (!is_dir(dirname($path))) {
                mkdir(dirname($path), 0755, true);
            }

            file_put_contents($path, 'test-installed');
            $this->createdInstallLock = true;
        }
    }

    protected function tearDown(): void
    {
        if ($this->createdInstallLock) {
            @unlink(InstallState::lockPath());
        }

        parent::tearDown();
    }

    public function test_installation_pages_are_not_exposed_after_installation(): void
    {
        $this->get('/install')->assertNotFound();
        $this->get('/install/database')->assertNotFound();
        $this->get('/install/admin')->assertNotFound();
        $this->get('/install/finished')->assertNotFound();
    }
}
