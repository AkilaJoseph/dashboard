<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

/**
 * Base class for pure unit tests.
 *
 * Extends PHPUnit directly — no Laravel container, no database.
 * Keep assertions fast; stub/mock all I/O.
 */
abstract class UnitTestCase extends BaseTestCase
{
    //
}
