<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Phpunit;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\MockedClassMethod;
use QratorLabs\Smocky\EmptyClass;
use ReflectionException;

class MockedMethod
{
    /** @var MockedClassMethod */
    private $mockedMethod;

    /** @var InvocationMocker */
    private $invocationMocker;

    /** @var MockObject */
    private $mockObject;

    /**
     * MockedMethod constructor.
     *
     * @param TestCase             $testCase
     * @param string               $class
     * @param string               $method
     * @param InvocationOrder|null $invocationRule
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     */
    public function __construct(
        TestCase $testCase,
        string $class,
        string $method,
        InvocationOrder $invocationRule = null
    ) {
        $mockObject = $testCase->getMockBuilder(EmptyClass::class)
                               ->disableOriginalConstructor()
                               ->disableOriginalClone()
                               ->disableArgumentCloning()
                               ->disallowMockingUnknownTypes()
                               ->addMethods([$method])
                               ->getMock();

        $this->mockObject = $mockObject;
        if ($invocationRule === null) {
            $this->invocationMocker = $mockObject->method($method);
        } else {
            $this->invocationMocker = $mockObject->expects($invocationRule)->method($method);
        }

        $this->mockedMethod = new MockedClassMethod(
            $class,
            $method,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             */
            static function (...$args) use ($mockObject, $method) {
                return $mockObject->{$method}(...$args);
            }
        );
    }

    public function getMocker(): InvocationMocker
    {
        return $this->invocationMocker;
    }
}
