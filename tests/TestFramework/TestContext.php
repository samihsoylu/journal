<?php

declare(strict_types=1);

namespace Tests\TestFramework;

use Tests\TestFramework\TestOrm\TestOrm;

/**
 * Global context holder for test infrastructure.
 *
 * This allows factories to optionally persist entities without
 * requiring explicit TestOrm injection in every factory call.
 *
 * When TestContext::$testOrm is set (in integration tests), factories
 * will automatically persist created entities. When null (in unit tests
 * or standalone usage), entities remain in-memory only.
 */
final class TestContext
{
    public static ?TestOrm $testOrm = null;
}
