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
    /**
     * @var MockedClassMethod
     */
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
        $this->mockObject = $testCase
            ->getMockBuilder(EmptyClass::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods([$method])
            ->getMock();

        if ($invocationRule === null) {
            $this->invocationMocker = $this->mockObject->method($method);
        } else {
            $this->invocationMocker = $this->mockObject->expects($invocationRule)->method($method);
        }

        $mockObject         = $this->mockObject;
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

    /**
     * @param object $object
     * @param mixed  ...$args
     *
     * @return void
     * @throws ReflectionException
     */
    public function callOriginal($object, ...$args)
    {
        $this->mockedMethod->callOriginal($object, ...$args);
    }

    /**
     * @param mixed ...$args
     *
     * @return void
     * @throws ReflectionException
     */
    public function callOriginalStatic(...$args)
    {
        $this->mockedMethod->callOriginalStatic(...$args);
    }

    public function getMocker(): InvocationMocker
    {
        return $this->invocationMocker;
    }
}
