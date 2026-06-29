<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        $installedFile = storage_path('installed');
        if (! file_exists($installedFile)) {
            file_put_contents($installedFile, now()->toIso8601String());
        }
    }
}
