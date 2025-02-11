<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Phpunit;

use PHPUnit\Framework\MockObject\Generator as Generator_PHPUnit9;
use PHPUnit\Framework\MockObject\Generator\Generator as Generator_PHPUnit1x;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\EmptyClass;
use ReflectionException;
use ReflectionMethod;

use function class_exists;

abstract class AbstractMocked
{
    /**
     * @return MockObject&EmptyClass
     * @throws ReflectionException
     */
    protected static function createEmptyMock(TestCase $testCase, string $method): MockObject
    {
        $generatorClass = class_exists(Generator_PHPUnit1x::class)
            ? Generator_PHPUnit1x::class
            : Generator_PHPUnit9::class;

        switch ($generatorClass) {
            case Generator_PHPUnit1x::class:
                $generatorMethod = 'testDouble';
                $args            = [
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
                if (count($args) > (new ReflectionMethod($generatorClass, 'testDouble'))->getNumberOfParameters()) {
                    // 10 -> 11 transition
                    unset($args[2]);
                }
                break;

            case Generator_PHPUnit9::class:
                $generatorMethod = 'getMock';
                $args            = [
                    EmptyClass::class,  // string $type
                    [$method],          // $methods = []
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
                break;
            default:
                throw new ReflectionException('Unknown PHPUnit version');
        }

        // @phpstan-ignore-next-line
        $mockObject = (new $generatorClass())->$generatorMethod(...$args);
        if (!$mockObject instanceof MockObject || !$mockObject instanceof EmptyClass) {
            throw new ReflectionException('Failed to create a mock object');
        }

        $testCase->registerMockObject($mockObject);

        return $mockObject;
    }
}
