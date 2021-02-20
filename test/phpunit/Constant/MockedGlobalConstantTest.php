<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\MockedGlobalConstant;
use ReflectionException;

use function constant;
use function define;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertSame;
use function uniqid;

/**
 * @internal
 */
class MockedGlobalConstantTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        $constantName  = uniqid('CONST_', false);
        $originalValue = uniqid('VALUE_', true);
        assertFalse(defined($constantName));

        define($constantName, $originalValue);
        assertSame($originalValue, constant($constantName));

        $mockedValue = 'someValue';
        assertNotEquals($mockedValue, $originalValue);
        $mockedConstant = new MockedGlobalConstant($constantName, $mockedValue);

        assertSame($mockedValue, constant($constantName));

        unset($mockedConstant);
        assertSame($originalValue, constant($constantName));
    }
}
