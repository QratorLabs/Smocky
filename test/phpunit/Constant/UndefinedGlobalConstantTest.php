<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\UndefinedGlobalConstant;
use ReflectionException;

use function constant;
use function define;
use function defined;
use function uniqid;

/**
 * @internal
 */
class UndefinedGlobalConstantTest extends TestCase
{
    public function testMissingConstantException(): void
    {
        $this->expectException(ReflectionException::class);
        new UndefinedGlobalConstant('CONST_UNDEFINED');
    }

    public function testOnlyUserDefined(): void
    {
        $this->expectWarning();
        new UndefinedGlobalConstant('PHP_VERSION');
    }

    /**
     * @throws ReflectionException
     */
    public function testUndefinedComplex(): void
    {
        $constantName = uniqid('CONST_', false);
        define($constantName, uniqid('VALUE_', true));

        $originalValue = constant($constantName);

        // check #0: validate test data
        self::assertTrue(defined($constantName));

        // task #1: remove constant
        $mockedConst = new UndefinedGlobalConstant($constantName);

        // check #1: constant is missing
        self::assertFalse(defined($constantName));

        // check #2: stored value is safe
        self::assertSame($originalValue, $mockedConst->getValue());

        // task #2: revert changes
        unset($mockedConst);

        self::assertSame($originalValue, constant($constantName));
    }
}
