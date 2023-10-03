<?php

declare(strict_types=1);

use SamihSoylu\Journal\Framework\Kernel;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestKit;
use SamihSoylu\Journal\Tests\TestFramework\Core\TestOrm\TestOrmTransactionInterface;

/*
|--------------------------------------------------------------------------
| Test Case
|--------------------------------------------------------------------------
|
| The closure you provide to your test functions is always bound to a specific PHPUnit test
| case class. By default, that class is "PHPUnit\Framework\TestCase". Of course, you may
| need to change it using the "uses()" function to bind a different classes or traits.
|
*/

uses()->group('unit')->in('src/Unit', 'framework/Unit');

uses()
    ->beforeEach(function (): void {
        $transaction = testKit()->getService(TestOrmTransactionInterface::class);
        $transaction->beginTransaction();
    })->afterEach(function (): void {
        $transaction = testKit()->getService(TestOrmTransactionInterface::class);
        $transaction->rollback();
    })->group('integration')->in('src/Integration');

uses()->group('architecture')->in('architecture');
/*
|--------------------------------------------------------------------------
| Expectations
|--------------------------------------------------------------------------
|
| When you're writing tests, you often need to check that values meet certain conditions. The
| "expect()" function gives you access to a set of "expectations" methods that you can use
| to assert different things. Of course, you may extend the Expectation API at any time.
|
*/


/*
|--------------------------------------------------------------------------
| Functions
|--------------------------------------------------------------------------
|
| While Pest is very powerful out-of-the-box, you may have some testing code specific to your
| project that you don't want to repeat in every file. Here you can also expose helpers as
| global functions to help you to reduce the number of lines of code in your test files.
|
*/

function testKit(): TestKit
{
    static $kernel = null;

    if ($kernel === null) {
        $kernel = Kernel::boot();
    }

    return $kernel->container->get(TestKit::class);
}
