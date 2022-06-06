<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Phpunit;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Phpunit\MockedFunction;
use ReflectionException;

class MockedFunctionTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        /** @see \QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction */
        $function    = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $originValue = $function();

        self::assertNotNull($originValue);
        $functionMock = new MockedFunction($this, $function);

        self::assertNull($function());
        unset($functionMock);
        self::assertSame($originValue, $function());
    }

    /**
     * @throws ReflectionException
     */
    public function testExpectNever(): void
    {
        $function = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        new MockedFunction($this, $function, self::never());
    }

    /**
     * @throws ReflectionException
     */
    public function testExpectOnce(): void
    {
        $function     = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $expected     = uniqid('', true);
        $functionMock = new MockedFunction($this, $function, self::once());
        $functionMock->getMocker()->willReturn($expected);
        self::assertSame($expected, $function());
    }

    /**
     * @return void
     * @throws ReflectionException
     */
    public function testCallOriginal(): void
    {
        /** @see \QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction */
        $function      = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $originalValue = $function();
        $extValue      = null;

        $mock = new MockedFunction($this, $function, self::once());
        // This was a bit tricky: we have to use `&$mock` to maintain variable-ref but not just object-ref
        // to do proper object destruction
        $mock->getMocker()->willReturnCallback(
            static function () use (&$mock, &$extValue) {
                $extValue = $mock->callOriginal();

                return 'someFunction';
            }
        );

        // is there any change?
        self::assertNotEquals($originalValue, $function());

        // call from outside
        self::assertEquals($originalValue, $mock->callOriginal());

        // call from closure
        self::assertEquals($originalValue, $extValue);

        // assigment is used instead of `unset` because closure have link (ref) to mock-object
        // unsetting of local variable will not destruct object, but assigning variable to `null`
        // will do the job
        $mock = null;
        self::assertEquals($originalValue, $function());
    }
}
