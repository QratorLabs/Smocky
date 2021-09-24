<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Functions;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Functions\MockedFunction;
use ReflectionException;
use ReflectionFunction;

use function is_file;
use function microtime;
use function PHPUnit\Framework\assertIsFloat;
use function PHPUnit\Framework\assertIsString;
use function PHPUnit\Framework\assertNotEquals;
use function PHPUnit\Framework\assertSame;
use function uniqid;

class MockedFunctionTest extends TestCase
{

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
        assertSame($expected, $function(__FILE__));
        unset($mock);
        assertSame($originalValue, $function(__FILE__));
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
        assertSame($expected, $function());
        unset($mock);
        assertSame($originalValue, $function());
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
        assertNotEquals($refC->getReturnType()->getName(), $refO->getReturnType()->getName());

        $mock = new MockedFunction($function, $closure);
        assertSame($expected, $function());
        assertIsFloat($function());
        unset($mock);
        assertSame($originalValue, $function());
        assertIsString($function());
    }

    public function testDefaultClosure(): void
    {
        $mock = new MockedFunction('is_file');
        self::assertNull(is_file(__FILE__));
        unset($mock);
        self::assertNotNull(is_file(__FILE__));
    }
}
