<?php

namespace Tests\Feature;

use Tests\TestCase;

class InstallationRoutesTest extends TestCase
{
    public function test_installation_pages_are_not_exposed_after_installation(): void
    {
        $this->get('/install')->assertNotFound();
        $this->get('/install/database')->assertNotFound();
        $this->get('/install/admin')->assertNotFound();
        $this->get('/install/finished')->assertNotFound();
    }
}
