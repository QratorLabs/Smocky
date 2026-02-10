<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use QratorLabs\Smocky\Constant\MockedGlobalConstant;
use QratorLabs\Smocky\Test\PhpUnit\RunkitDependantTestCase;
use ReflectionException;

use function constant;
use function define;
use function defined;
use function uniqid;

/**
 * @internal
 */
class MockedGlobalConstantTest extends RunkitDependantTestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        /** @noinspection PhpRedundantOptionalArgumentInspection */
        $constantName  = uniqid('CONST_', false);
        $originalValue = uniqid('VALUE_', true);
        self::assertFalse(defined($constantName));

        define($constantName, $originalValue);
        self::assertSame($originalValue, constant($constantName));

        $mockedValue = 'someValue';
        self::assertNotEquals($mockedValue, $originalValue);
        $mockedConstant = new MockedGlobalConstant($constantName, $mockedValue);

        self::assertSame($mockedValue, constant($constantName));

        unset($mockedConstant);
        self::assertSame($originalValue, constant($constantName));
    }
}
