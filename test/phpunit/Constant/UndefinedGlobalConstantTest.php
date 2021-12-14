<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\UndefinedGlobalConstant;
use ReflectionException;

use function constant;
use function define;
use function defined;
use function PHPUnit\Framework\assertFalse;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\assertTrue;
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
        assertTrue(defined($constantName));

        // task #1: remove constant
        $mockedConst = new UndefinedGlobalConstant($constantName);

        // check #1: constant is missing
        assertFalse(defined($constantName));

        // check #2: stored value is safe
        assertSame($originalValue, $mockedConst->getValue());

        // task #2: revert changes
        unset($mockedConst);

        assertSame($originalValue, constant($constantName));
    }
}
