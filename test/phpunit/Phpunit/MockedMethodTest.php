<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Phpunit;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Phpunit\MockedMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use ReflectionException;

use function uniqid;

/**
 * @internal
 */
class MockedMethodTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        $object      = new ClassWithMethods();
        $originValue = $object->publicMethod();
        self::assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicMethod');
        self::assertNull($object->publicMethod());
        unset($methodMock);
        self::assertSame($originValue, $object->publicMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testMinimalStatic(): void
    {
        $originValue = ClassWithMethods::publicStaticMethod();
        self::assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicStaticMethod');
        self::assertNull(ClassWithMethods::publicStaticMethod());
        unset($methodMock);
        self::assertSame($originValue, ClassWithMethods::publicStaticMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testExpectNever(): void
    {
        new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicStaticMethod',
            self::never()
        );
    }

    /**
     * @throws ReflectionException
     */
    public function testExpectOnce(): void
    {
        $expected   = uniqid('', true);
        $methodMock = new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicStaticMethod',
            self::once()
        );
        $methodMock->getMocker()->willReturn($expected);
        self::assertSame($expected, ClassWithMethods::publicStaticMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testCallOriginalStatic(): void
    {
        $methodMock = new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicStaticMethod',
            self::never()
        );
        $methodMock->callOriginalStatic();
    }

    /**
     * @throws ReflectionException
     */
    public function testCallOriginal(): void
    {
        $object     = new ClassWithMethods();
        $methodMock = new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicMethod',
            self::never()
        );
        $methodMock->callOriginal($object);
    }
}
