<?php

namespace Tests;

use Illuminate\Foundation\Testing\TestCase as BaseTestCase;
use Illuminate\Support\Facades\DB;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        // Register REGEXP function for SQLite
        if (DB::connection()->getDriverName() === 'sqlite') {
            DB::connection()->getPdo()->sqliteCreateFunction('REGEXP', function ($pattern, $value) {
                // Use # as delimiter to avoid conflicts with / in patterns
                return preg_match('#'.$pattern.'#', $value) ? 1 : 0;
            }, 2);
        }
    }
}
