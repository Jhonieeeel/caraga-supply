<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Spatie\Permission\PermissionRegistrar;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Spatie's permission/role cache is not automatically invalidated by
        // RefreshDatabase between tests, which can leak stale grants from one
        // test into the next when roles/permissions are recreated with the
        // same auto-incremented ids. Clear it fresh for every test.
        $this->app->make(PermissionRegistrar::class)->forgetCachedPermissions();
    }
}
