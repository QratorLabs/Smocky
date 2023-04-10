<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Smocky;

use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\MockedEntity;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithConstants;
use QratorLabs\Smocky\Test\PhpUnit\Helpers\ClassWithMethods;
use ReflectionClassConstant;
use ReflectionException;
use ReflectionMethod;

use function constant;
use function strtoupper;

/**
 * @internal
 */
class MockedEntityTest extends TestCase
{
    public function testStashedName(): void
    {
        // helper not call private function without reflection
        $helper = new class extends MockedEntity {
            public function __destruct()
            {
                // nothing to do here
            }

            /**
             * @param string $basename
             * @param string $prefix
             *
             * @return string
             */
            public function getStashedNamePublic(string $basename, string $prefix = ''): string
            {
                return $this->getStashedName($basename, $prefix);
            }
        };

        // check #1: contains basename
        $basename = 'someName';
        self::assertStringContainsString($basename, $helper->getStashedNamePublic($basename));

        // check #2: contains basename and prefix
        $prefix = 'somePrefix';
        $name   = $helper->getStashedNamePublic($basename, $prefix);
        self::assertStringContainsString($basename, $name);
        self::assertStringContainsString($prefix, $name);

        // check #3: unique
        // yep, I know that we can't call `uniqid()` in true parallel,
        // but let's make it look like pretty close calls
        self::assertNotSame($helper->getStashedNamePublic($basename), $helper->getStashedNamePublic($basename));
    }

    /**
     * @throws ReflectionException
     */
    public function testGetVisibility(): void
    {
        $mock = new class extends MockedEntity {
            public function __destruct()
            {
            }
        };
        $method = new ReflectionMethod(MockedEntity::class, 'getVisibility');
        $method->setAccessible(true);

        foreach (ClassWithConstants::getDataForTests() as $key => $item) {
            self::assertEquals(
                constant('RUNKIT7_ACC_' . strtoupper($key)),
                $method->invoke($mock, new ReflectionClassConstant($item[0], $item[1]))
            );
        }
        foreach (ClassWithMethods::getDataForTests() as $key => $item) {
            [$vis] = explode(' ', $key);
            self::assertEquals(
                constant('RUNKIT7_ACC_' . strtoupper($vis)),
                $method->invoke($mock, new ReflectionMethod($item[0], $item[1]))
            );
        }
    }
}
