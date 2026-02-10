<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit;

use Error;
use PHPUnit\Framework\TestCase;

use function extension_loaded;

/**
 * @internal
 */
abstract class RunkitDependantTestCase extends TestCase
{
    protected function setUp(): void
    {
        if (!extension_loaded('runkit7')) {
            // In case of missing extension, we want to be sure that tests are executed and fail with proper message
            $this->markTestSkipped('The runkit7 extension is not loaded. Please install it to run this test.');
        }
        parent::setUp();
    }
}
