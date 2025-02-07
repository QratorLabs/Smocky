<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Functions;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Functions\MockedFunction;
use ReflectionException;
use ReflectionFunction;

use function is_file;
use function microtime;
use function uniqid;

class MockedFunctionTest extends TestCase
{
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

        $mock    = null;
        $closure = static function () use (&$mock, &$extValue) {
            /** @var MockedFunction $mock */
            $extValue = $mock->callOriginal();

            return 'someString';
        };
        $mock = new MockedFunction($function, $closure);

        self::assertNotEquals($originalValue, $function());
        self::assertEquals($originalValue, $mock->callOriginal());
        self::assertEquals($originalValue, $extValue);
        // assigment is used instead of `unset` because closure have link (ref) to mock-object
        // unsetting of local variable will not destruct object, but assigning variable to `null`
        // will do the job
        $mock = null;
        self::assertEquals($originalValue, $function());
    }

    /**
     * @throws ReflectionException
     */
    public function testChangeReturnType(): void
    {
        /** @see \QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction */
        $function      = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $originalValue = $function();

        $expected = microtime(true);
        $closure  = static function () use ($expected): float {
            return $expected;
        };

        $refO = new ReflectionFunction($function);
        $refC = new ReflectionFunction($closure);

        /** @phpstan-ignore-next-line */
        self::assertNotEquals($refC->getReturnType()->getName(), $refO->getReturnType()->getName());

        $mock = new MockedFunction($function, $closure);
        self::assertSame($expected, $function());
        self::assertIsFloat($function());
        unset($mock);
        self::assertSame($originalValue, $function());
        self::assertIsString($function());
    }

    public function testDefaultClosure(): void
    {
        $mock = new MockedFunction('is_file');
        // @phpstan-ignore-next-line - we are overriding global function
        self::assertNull(is_file(__FILE__));
        unset($mock);
        self::assertNotNull(is_file(__FILE__));
    }

    /**
     * @throws ReflectionException
     */
    public function testGlobalFunction(): void
    {
        $function      = 'is_file';
        $originalValue = $function(__FILE__);

        $expected = uniqid('', true);
        $mock     = new MockedFunction($function, static function () use ($expected): string {
            return $expected;
        });
        self::assertSame($expected, $function(__FILE__));
        unset($mock);
        self::assertSame($originalValue, $function(__FILE__));
    }

    /**
     * @throws ReflectionException
     */
    public function testNamespacedFunction(): void
    {
        $function      = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $originalValue = $function();

        $expected = uniqid('', true);
        $mock     = new MockedFunction($function, static function () use ($expected): string {
            return $expected;
        });
        self::assertSame($expected, $function());
        unset($mock);
        self::assertSame($originalValue, $function());
    }
}
