<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Phpunit;

use PHPUnit\Framework\MockObject\Generator\Generator;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\EmptyClass;
use ReflectionMethod;

use function assert;

abstract class AbstractMocked
{
    /**
     * @return MockObject&EmptyClass
     */
    protected static function createEmptyMock(TestCase $testCase, string $method): MockObject
    {
        $args = [
            EmptyClass::class,  // string $type
            true,               // bool $mockObject
            true,               // bool $markAsMockObject
            [$method],          // ?array $methods = []
            [],                 // array $arguments = []
            '',                 // string $mockClassName = ''
            false,              // bool $callOriginalConstructor = true
            false,              // bool $callOriginalClone = true
            true,               // bool $callAutoload = true
            false,              // bool $cloneArguments = true
            false,              // bool $callOriginalMethods = false
            null,               // object $proxyTarget = null
            false,              // bool $allowMockingUnknownTypes = true
            true,               // bool $returnValueGeneration = true
        ];

        if (count($args) > (new ReflectionMethod(Generator::class, 'testDouble'))->getNumberOfParameters()) {
            // 10 -> 11 transition
            unset($args[2]);
        }

        $mockObject = (new Generator())->testDouble(...$args);
        assert($mockObject instanceof EmptyClass);
        assert($mockObject instanceof MockObject);
        $testCase->registerMockObject($mockObject);

        return $mockObject;
    }
}
