<?php

namespace Tests;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Helpers\CreatesTestData;

/**
 * Base class for all Feature tests.
 *
 * Wraps each test in a transaction and rolls back — keeps :memory: SQLite fast.
 * Use CreatesTestData helpers instead of raw Model::create() calls in tests.
 */
abstract class FeatureTestCase extends TestCase
{
    use RefreshDatabase;
    use CreatesTestData;
}
