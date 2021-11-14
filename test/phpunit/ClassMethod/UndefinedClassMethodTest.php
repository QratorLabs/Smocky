<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\UndefinedClassMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use ReflectionClass;
use ReflectionException;
use ReflectionMethod;

use function array_reduce;
use function PHPUnit\Framework\assertContains;
use function PHPUnit\Framework\assertNotContains;

/**
 * @internal
 */
class UndefinedClassMethodTest extends TestCase
{
    public function testMissingMethod(): void
    {
        $this->expectException(ReflectionException::class);
        new UndefinedClassMethod(ClassWithMethods::class, 'NOT_EXISTING_METHOD');
    }

    /**
     * @param string $class
     * @param string $method
     *
     * @throws ReflectionException
     *
     * @dataProvider \QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods::getDataForTests()
     *
     * @phpstan-param class-string<ClassWithMethods> $class
     * @noinspection PhpUndefinedClassInspection
     */
    public function testRemoveMethod(string $class, string $method): void
    {
        $classReflection = new ReflectionClass($class);
        assertContains($method, self::helperGetReflectionMethodNames($classReflection));

        $undefinedMethod = new UndefinedClassMethod($class, $method);
        assertNotContains($method, self::helperGetReflectionMethodNames($classReflection));

        unset($undefinedMethod);
        assertContains($method, self::helperGetReflectionMethodNames($classReflection));
    }

    /**
     * @param ReflectionClass<ClassWithMethods> $classReflection
     *
     * @return string[]
     */
    private static function helperGetReflectionMethodNames(ReflectionClass $classReflection): array
    {
        return array_reduce(
            $classReflection->getMethods(),
            static function (array $acc, ReflectionMethod $method): array {
                $acc[] = $method->name;

                return $acc;
            },
            []
        );
    }
}
