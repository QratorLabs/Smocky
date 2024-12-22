<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\UndefinedGlobalConstant;
use ReflectionException;
use RuntimeException;
use Throwable;

use function constant;
use function define;
use function defined;
use function get_class;
use function set_error_handler;
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
        $ex  = new class extends RuntimeException {
        };
        $cls = get_class($ex);

        set_error_handler(static function (int $errno, string $errstr) use ($cls) {
            throw new $cls($errstr, $errno);
        });

        $this->expectException($cls);
        try {
            new UndefinedGlobalConstant('PHP_VERSION');
        } finally {
            restore_error_handler();
        }
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
