<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Phpunit;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Phpunit\MockedMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use ReflectionException;

use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;
use function PHPUnit\Framework\never;
use function PHPUnit\Framework\once;
use function uniqid;

class MockedMethodTest extends TestCase
{

    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        $object      = new ClassWithMethods();
        $originValue = $object->publicMethod();
        assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicMethod');
        assertNull($object->publicMethod());
        unset($methodMock);
        assertSame($originValue, $object->publicMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testMinimalStatic(): void
    {
        $originValue = ClassWithMethods::publicStaticMethod();
        assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicStaticMethod');
        assertNull(ClassWithMethods::publicStaticMethod());
        unset($methodMock);
        assertSame($originValue, ClassWithMethods::publicStaticMethod());
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
            never()
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
            once()
        );
        $methodMock->getMocker()->willReturn($expected);
        assertSame($expected, ClassWithMethods::publicStaticMethod());
    }
}
