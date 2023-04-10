<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use Generator;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\UndefinedClassConstant;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithConstants;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;
use RuntimeException;
use Throwable;

use function get_class;
use function set_error_handler;

use const E_WARNING;

/**
 * @internal
 */
class UndefinedClassConstantTest extends TestCase
{
    public function testMissingConstantException(): void
    {
        $this->expectException(ReflectionException::class);
        new UndefinedClassConstant(ClassWithConstants::class, 'CONST_UNDEFINED');
    }

    /**
     * @return Generator<string, array{string, string}>
     */
    public function dataCoreConstants(): Generator
    {
        yield 'ReflectionClass::IS_FINAL' => [ReflectionClass::class, 'IS_FINAL'];
    }

    /**
     * @param string               $class
     * @param string               $constantName
     *
     * @throws ReflectionException
     * @throws Throwable
     *
     * @dataProvider dataCoreConstants
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     *
     * This test ensures that php core constants couldn't be mocked
     */
    public function testCoreConstants(string $class, string $constantName): void
    {
        $ex = new class extends RuntimeException {
        };
        $cls = get_class($ex);

        $prev = set_error_handler(static function (int $errno, string $errstr) use ($cls) {
            throw new $cls($errstr, $errno);
        }, E_WARNING);


        $this->expectException($cls);
        try {
            new UndefinedClassConstant($class, $constantName);
        } catch (Throwable $ex) {
            if ($prev) {
                set_error_handler($prev);
            }
            throw $ex;
        }
    }

    /**
     * @param string               $class
     * @param string               $constantName
     *
     * @throws ReflectionException
     *
     * @dataProvider \QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithConstants::getDataForTests()
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     */
    public function testRemoveCheckWithReflection(string $class, string $constantName): void
    {
        // check #1: reflection is working
        $constantReflection = new ReflectionClassConstant($class, $constantName);
        unset($constantReflection);

        // task #1: mock constant
        /**
         * @noinspection PhpUnusedLocalVariableInspection
         * variable is in use - it prevents object of calling __destruct
         */
        $mockedConstant = new UndefinedClassConstant($class, $constantName);

        // check #2: get ready for exception
        $this->expectException(ReflectionException::class);

        // task #2: try to create reflection (should throw exception)
        new ReflectionClassConstant($class, $constantName);
    }

    /**
     * @param string               $class
     * @param string               $constantName
     *
     * @throws ReflectionException
     *
     * @dataProvider \QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithConstants::getDataForTests()
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     */
    public function testRemoveComplex(string $class, string $constantName): void
    {
        $classReflection    = new ReflectionClass($class);
        $constantReflection = new ReflectionClassConstant($class, $constantName);
        $originalValue      = $constantReflection->getValue();
        unset($constantReflection);

        // check #0: validate test data
        self::assertArrayHasKey($constantName, $classReflection->getConstants());

        // task #1: remove constant
        $mockedConst = new UndefinedClassConstant($class, $constantName);

        // check #1: constant is missing
        self::assertArrayNotHasKey($constantName, $classReflection->getConstants());

        // check #2: stored value is safe
        self::assertSame($originalValue, $mockedConst->getValue());

        // task #2: revert changes
        unset($mockedConst);

        $constantReflection = new ReflectionClassConstant($class, $constantName);
        $revertedValue      = $constantReflection->getValue();
        self::assertSame($originalValue, $revertedValue);
    }
}
