<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Constant;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Constant\MockedClassConstant;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithConstants;
use ReflectionClass;
use ReflectionClassConstant;
use ReflectionException;

use function uniqid;

/**
 * @internal
 */
class MockedClassConstantTest extends TestCase
{
    /**
     * @return Generator
     * @phpstan-return Generator<string, array{class-string, string}>
     */
    public static function getDataForTests(): Generator
    {
        return ClassWithConstants::getDataForTests();
    }

    public function testMissingConstantException(): void
    {
        $this->expectException(ReflectionException::class);
        new MockedClassConstant(ClassWithConstants::class, 'CONST_UNDEFINED', null);
    }

    /**
     * @param string               $class
     * @param string               $constantName
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     */
    #[DataProvider("getDataForTests")]
    public function testUsageComplex(string $class, string $constantName): void
    {
        $classReflection    = new ReflectionClass($class);
        $constantReflection = new ReflectionClassConstant($class, $constantName);
        $originalValue      = $constantReflection->getValue();
        $newValue           = uniqid('', true);

        // task #1: create object (and mock constant)
        $mockedConst = new MockedClassConstant($class, $constantName, $newValue);

        // check #1: value override (main target)
        self::assertSame($newValue, $classReflection->getConstants()[$constantName]);
        // check #2: stored value
        self::assertSame($originalValue, $mockedConst->getValue());

        // task #2: destroy object (and revert changes)
        unset($mockedConst);

        // check #3: changes are revered
        self::assertSame($originalValue, $classReflection->getConstants()[$constantName]);
    }

    /**
     * @throws ReflectionException
     */
    public function testUsagePublic(): void
    {
        $originalValue = ClassWithConstants::CONST_PUBLIC;

        // task #1: create object
        $mockedConst = new MockedClassConstant(ClassWithConstants::class, 'CONST_PUBLIC', 222);

        // check #1: value override (main target)
        self::assertSame(222, ClassWithConstants::CONST_PUBLIC);

        // check #1: stored value
        self::assertSame($originalValue, $mockedConst->getValue());

        // task #2: remove object - changes should be reverted
        unset($mockedConst);

        // check #3: constant now having original value
        self::assertSame($originalValue, ClassWithConstants::CONST_PUBLIC);
    }
}
