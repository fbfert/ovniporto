<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Feature tests check routes and Inertia props, not compiled assets:
        // never depend on the Vite manifest or a running dev server.
        $this->withoutVite();
    }
}
