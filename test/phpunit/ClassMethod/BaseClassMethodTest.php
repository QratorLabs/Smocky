<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\BaseClassMethod;

use function PHPUnit\Framework\assertSame;
use function uniqid;

/**
 * @internal
 */
class BaseClassMethodTest extends TestCase
{
    public function testClassMethodGetters(): void
    {
        $class  = self::class;
        $method = strtr(uniqid('', true), '.', '_');

        $dummy = new class ($class, $method) extends BaseClassMethod {
            public function __construct(string $class, string $method)
            {
                $this->class  = $class;
                $this->method = $method;
            }

            public function __destruct()
            {
                // nothing to do here
            }
        };

        assertSame($class, $dummy->getClass());
        assertSame($method, $dummy->getMethod());
    }
}
