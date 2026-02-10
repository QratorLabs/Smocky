<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use Generator;
use PHPUnit\Framework\Attributes\DataProvider;
use QratorLabs\Smocky\ClassMethod\UndefinedClassMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use QratorLabs\Smocky\Test\PhpUnit\RunkitDependantTestCase;
use ReflectionClass;
use ReflectionException;

/**
 * @internal
 */
class UndefinedClassMethodTest extends RunkitDependantTestCase
{
    /**
     * @return Generator
     * @phpstan-return Generator<string, array{class-string, string}>
     * @throws ReflectionException
     */
    public static function getDataForTests(): Generator
    {
        return ClassWithMethods::getDataForTests();
    }

    /**
     * @param ReflectionClass<ClassWithMethods> $classReflection
     *
     * @return string[]
     */
    private static function helperGetReflectionMethodNames(ReflectionClass $classReflection): array
    {
        /** @var string[] $result */
        $result = [];
        foreach ($classReflection->getMethods() as $method) {
            $result[] = $method->name;
        }

        return $result;
    }

    public function testMissingMethod(): void
    {
        $this->expectException(ReflectionException::class);
        new UndefinedClassMethod(ClassWithMethods::class, 'NOT_EXISTING_METHOD');
    }

    /**
     * @param string                                 $class
     * @param string                                 $method
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string<ClassWithMethods> $class
     */
    #[DataProvider('getDataForTests')]
    public function testRemoveMethod(string $class, string $method): void
    {
        $classReflection = new ReflectionClass($class);
        self::assertContains($method, self::helperGetReflectionMethodNames($classReflection));

        $undefinedMethod = new UndefinedClassMethod($class, $method);
        self::assertNotContains($method, self::helperGetReflectionMethodNames($classReflection));

        unset($undefinedMethod);
        self::assertContains($method, self::helperGetReflectionMethodNames($classReflection));
    }
}
