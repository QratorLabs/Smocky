<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Functions;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\Functions\UndefinedFunction;
use ReflectionException;

class UndefinedFunctionTest extends TestCase
{

    public function testMissingFunction(): void
    {
        $this->expectException(ReflectionException::class);
        new UndefinedFunction('someNotExistingFunction');
    }
}
