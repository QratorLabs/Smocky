<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\MockedClassMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ExtendedClassWithMethods;
use ReflectionException;

use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function uniqid;

class MockedClassMethodTest extends TestCase
{

    public function testMissingMethod(): void
    {
        $this->expectException(ReflectionException::class);
        new MockedClassMethod(
            ClassWithMethods::class,
            'NOT_EXISTING_METHOD'
        );
    }

    public function testMockRuntime(): void
    {
        $object        = new ClassWithMethods();
        $originalValue = $object->publicMethod();
        $expected      = uniqid('', true);
        $method        = new MockedClassMethod(
            ClassWithMethods::class,
            'publicMethod',
            static function () use ($expected): string {
                return $expected;
            }
        );
        assertSame($expected, $object->publicMethod());
        unset($method);
        assertSame($originalValue, $object->publicMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testMockStatic(): void
    {
        $originalValue = ClassWithMethods::publicStaticMethod();
        $expected      = uniqid('', true);
        $method        = new MockedClassMethod(
            ClassWithMethods::class,
            'publicStaticMethod',
            static function () use ($expected): string {
                return $expected;
            }
        );
        assertSame($expected, ClassWithMethods::publicStaticMethod());
        unset($method);
        assertSame($originalValue, ClassWithMethods::publicStaticMethod());
    }

    public function testChildrenStubbing(): void
    {
        $child  = new ExtendedClassWithMethods();
        $method = new MockedClassMethod(ClassWithMethods::class, 'publicStaticMethod');
        assertNull(ClassWithMethods::publicStaticMethod());

        assertSame(ExtendedClassWithMethods::publicStaticMethod(), ClassWithMethods::publicStaticMethod());

        unset($method);
        unset($child);
    }
}
