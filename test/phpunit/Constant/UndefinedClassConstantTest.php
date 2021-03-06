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

use function PHPUnit\Framework\assertArrayHasKey;
use function PHPUnit\Framework\assertArrayNotHasKey;
use function PHPUnit\Framework\assertSame;

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
        $this->expectWarning();
        new UndefinedClassConstant($class, $constantName);
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
        assertArrayHasKey($constantName, $classReflection->getConstants());

        // task #1: remove constant
        $mockedConst = new UndefinedClassConstant($class, $constantName);

        // check #1: constant is missing
        assertArrayNotHasKey($constantName, $classReflection->getConstants());

        // check #2: stored value is safe
        assertSame($originalValue, $mockedConst->getValue());

        // task #2: revert changes
        unset($mockedConst);

        $constantReflection = new ReflectionClassConstant($class, $constantName);
        $revertedValue      = $constantReflection->getValue();
        assertSame($originalValue, $revertedValue);
    }
}
