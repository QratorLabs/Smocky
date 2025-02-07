<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\ClassMethod;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\BaseClassMethod;

use function uniqid;
use function str_replace;

/**
 * @internal
 */
class BaseClassMethodTest extends TestCase
{
    public function testClassMethodGetters(): void
    {
        $class  = self::class;
        $method = str_replace('.', '_', uniqid('', true));

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

        self::assertSame($class, $dummy->getClass());
        self::assertSame($method, $dummy->getMethod());
    }
}
