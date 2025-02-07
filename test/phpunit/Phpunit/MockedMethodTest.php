<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Phpunit;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Phpunit\MockedMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use ReflectionException;

use function get_class;
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
        // @phpstan-ignore-next-line - we need to check if method in it's original state
        self::assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicMethod');
        // @phpstan-ignore-next-line - we need to check if method in it's mocked state
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
        // @phpstan-ignore-next-line - we need to check if method in it's original state
        self::assertNotNull($originValue);
        $methodMock = new MockedMethod($this, ClassWithMethods::class, 'publicStaticMethod');
        // @phpstan-ignore-next-line - we need to check if method in it's mocked state
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
            $this->never()
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
            $this->once()
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
            $this->never()
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
            $this->never()
        );
        $methodMock->callOriginal($object);
    }

    /**
     * @throws ReflectionException
     */
    public function testCallOriginalWithReturn(): void
    {
        $object        = new ClassWithMethods();
        $originalValue = $object->publicMethod();
        $methodMock    = new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicMethod',
            $this->exactly(2)
        );
        $methodMock->getMocker()->willReturnCallback(static function () use ($object, $methodMock) {
            static $firstTime = true;
            if ($firstTime) {
                $firstTime = false;

                return $methodMock->callOriginal($object);
            }

            return 'mockedMethod';
        });
        self::assertSame($originalValue, $object->publicMethod());
        self::assertSame('mockedMethod', $object->publicMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testCallOriginalStaticWithReturn(): void
    {
        $originalValue = ClassWithMethods::publicStaticMethod();
        $methodMock    = new MockedMethod(
            $this,
            ClassWithMethods::class,
            'publicStaticMethod',
            $this->exactly(2)
        );
        $methodMock->getMocker()->willReturnCallback(static function () use (&$methodMock) {
            static $firstTime = true;
            if ($firstTime) {
                $firstTime = false;

                return $methodMock->callOriginalStatic();
            }

            return 'mockedMethod';
        });
        self::assertSame($originalValue, ClassWithMethods::publicStaticMethod());
        self::assertSame('mockedMethod', ClassWithMethods::publicStaticMethod());
        $methodMock = null;
        self::assertSame($originalValue, ClassWithMethods::publicStaticMethod());
    }

    /**
     * @throws ReflectionException
     */
    public function testCallOriginalWithSideEffect(): void
    {
        $object = new class {
            /** @var string */
            public $value = 'initial';

            public function getValue(): string
            {
                return $this->value;
            }
        };

        self::assertSame('initial', $object->getValue());
        $mock = new MockedMethod($this, get_class($object), 'getValue', $this->once());
        $mock->getMocker()->willReturnCallback(static function () use ($object) {
            $object->value = 'changed';

            return 'mocked';
        });

        self::assertSame('mocked', $object->getValue());
        unset($mock);
        self::assertSame('changed', $object->getValue());
        self::assertSame('changed', $object->value);
    }
}
