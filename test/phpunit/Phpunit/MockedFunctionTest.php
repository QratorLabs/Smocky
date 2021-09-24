<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Phpunit;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Phpunit\MockedFunction;
use ReflectionException;

use function PHPUnit\Framework\assertNotNull;
use function PHPUnit\Framework\assertNull;
use function PHPUnit\Framework\assertSame;

class MockedFunctionTest extends TestCase
{
    /**
     * @throws ReflectionException
     */
    public function testMinimal(): void
    {
        $function    = '\QratorLabs\Smocky\Test\PhpUnit\Helpers\someFunction';
        $originValue = $function();

        assertNotNull($originValue);
        $functionMock = new MockedFunction($this, $function);

        assertNull($function());
        unset($functionMock);
        assertSame($originValue, $function());
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
        assertSame($expected, $function());
    }
}
