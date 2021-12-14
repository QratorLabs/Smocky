<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\MockedClassMethod;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ExtendedClassWithMethods;
use ReflectionException;
use RuntimeException;

use function PHPUnit\Framework\assertSame;
use function uniqid;

/**
 * @internal
 */
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
        self::assertSame($expected, $object->publicMethod());
        unset($method);
        self::assertSame($originalValue, $object->publicMethod());
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
        self::assertSame($expected, ClassWithMethods::publicStaticMethod());
        unset($method);
        self::assertSame($originalValue, ClassWithMethods::publicStaticMethod());
    }

    public function testChildrenStubbing(): void
    {
        $child  = new ExtendedClassWithMethods();
        $method = new MockedClassMethod(ClassWithMethods::class, 'publicStaticMethod');
        self::assertNull(ClassWithMethods::publicStaticMethod());

        self::assertSame(ExtendedClassWithMethods::publicStaticMethod(), ClassWithMethods::publicStaticMethod());

        unset($method, $child);
    }

    public function testCallOriginalStaticPublic(): void
    {
        $originalValue = ClassWithMethods::publicStaticMethod();
        $extValue      = null;
        /** @var MockedClassMethod $method */
        $method = null;
        $method = new MockedClassMethod(
            ClassWithMethods::class,
            'publicStaticMethod',
            static function () use (&$method, &$extValue) {
                $extValue = $method->callOriginalStatic();

                return 'someString';
            }
        );

        self::assertNotEquals($originalValue, ClassWithMethods::publicStaticMethod());
        self::assertEquals($originalValue, $method->callOriginalStatic());
        self::assertEquals($originalValue, $extValue);
        // assigment is used instead of `unset` because closure have link (ref) to mock-object
        // unsetting of local variable will not destruct object, but assigning variable to `null`
        // will do the job
        $method = null;
        self::assertEquals($originalValue, ClassWithMethods::publicStaticMethod());
    }

    public function testCallOriginalStaticNotStatic(): void
    {
        $method = new MockedClassMethod(ClassWithMethods::class, 'publicMethod');
        $this->expectException(RuntimeException::class);
        $method->callOriginalStatic();
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalStaticProtected(): void
    {
        $method = new MockedClassMethod(ClassWithMethods::class, 'protectedStaticMethod');
        self::assertSame('protectedStaticMethod', $method->callOriginalStatic());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalStaticPrivate(): void
    {
        $method = new MockedClassMethod(ClassWithMethods::class, 'privateStaticMethod');
        self::assertSame('privateStaticMethod', $method->callOriginalStatic());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalPublic(): void
    {
        $object        = new ClassWithMethods();
        $originalValue = $object->publicMethod();
        $expected      = uniqid('', true);
        $extValue      = null;
        /** @var MockedClassMethod $method */
        $method = null;
        $method = new MockedClassMethod(
            ClassWithMethods::class,
            'publicMethod',
            static function () use ($expected, &$object, &$method, &$extValue): string {
                $extValue = $method->callOriginal($object);

                return $expected;
            }
        );

        self::assertNotSame($originalValue, $object->publicMethod());
        self::assertSame($originalValue, $method->callOriginal($object));
        self::assertSame($originalValue, $extValue);
        $method = null;
        self::assertSame($originalValue, $object->publicMethod());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalPublicInvalidObject(): void
    {
        $method = new MockedClassMethod(ClassWithMethods::class, 'publicMethod');
        $this->expectException(RuntimeException::class);
        $method->callOriginal($this);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalPublicStatic(): void
    {
        $object = new ClassWithMethods();
        $method = new MockedClassMethod(ClassWithMethods::class, 'publicStaticMethod');
        $this->expectException(RuntimeException::class);
        $method->callOriginal($object);
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalPrivate(): void
    {
        $object = new ClassWithMethods();
        $method = new MockedClassMethod(ClassWithMethods::class, 'privateMethod');
        assertSame('privateMethod', $method->callOriginal($object));
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginalProtected(): void
    {
        $object = new ClassWithMethods();
        $method = new MockedClassMethod(ClassWithMethods::class, 'protectedMethod');
        assertSame('protectedMethod', $method->callOriginal($object));
    }
}
