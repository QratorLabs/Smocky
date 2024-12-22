<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Phpunit;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\EmptyClass;
use QratorLabs\Smocky\Functions\MockedFunction as GenericMockedFunction;
use ReflectionException;

use function assert;

class MockedFunction
{
    /** @var InvocationMocker */
    private $invocationMocker;
    /** @var GenericMockedFunction */
    private $mockedFunction;

    /**
     * MockedMethod constructor.
     *
     * @param TestCase             $testCase
     * @param string               $function
     * @param InvocationOrder|null $invocationRule
     *
     * @throws ReflectionException
     */
    public function __construct(
        TestCase $testCase,
        string $function,
        ?InvocationOrder $invocationRule = null
    ) {
        $mockObject = null;
        $method     = null;

        $this->mockedFunction = new GenericMockedFunction(
            $function,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             */
            static function (...$args) use (&$mockObject, &$method) {
                return $mockObject->{$method}(...$args);
            }
        );

        $method = $this->mockedFunction->getShortName();
        assert($method !== '');
        $mockObject = $testCase
            ->getMockBuilder(EmptyClass::class)
            ->disableOriginalConstructor()
            ->disableOriginalClone()
            ->disableArgumentCloning()
            ->disallowMockingUnknownTypes()
            ->addMethods([$method])
            ->getMock();

        if ($invocationRule === null) {
            $this->invocationMocker = $mockObject->method($method);
        } else {
            $this->invocationMocker = $mockObject->expects($invocationRule)->method($method);
        }
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function callOriginal(...$args)
    {
        return $this->mockedFunction->callOriginal(...$args);
    }

    public function getMocker(): InvocationMocker
    {
        return $this->invocationMocker;
    }
}
