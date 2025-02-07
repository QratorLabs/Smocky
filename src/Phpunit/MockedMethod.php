<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Phpunit;

use PHPUnit\Framework\MockObject\Builder\InvocationMocker;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\MockObject\Rule\InvocationOrder;
use PHPUnit\Framework\TestCase;
use QratorLabs\Smocky\ClassMethod\MockedClassMethod;
use ReflectionException;

class MockedMethod extends AbstractMocked
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
     */
    public function __construct(
        TestCase $testCase,
        string $class,
        string $method,
        ?InvocationOrder $invocationRule = null
    ) {
        $this->mockObject = self::createEmptyMock($testCase, $method);

        if ($invocationRule === null) {
            /** @var InvocationMocker $mocker */
            $mocker = $this->mockObject->method($method);
        } else {
            $mocker = $this->mockObject->expects($invocationRule)->method($method);
        }
        $this->invocationMocker = $mocker;

        $mockObject         = $this->mockObject;
        $this->mockedMethod = new MockedClassMethod(
            $class,
            $method,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
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
     * @return mixed
     * @throws ReflectionException
     */
    public function callOriginal($object, ...$args)
    {
        return $this->mockedMethod->callOriginal($object, ...$args);
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function callOriginalStatic(...$args)
    {
        return $this->mockedMethod->callOriginalStatic(...$args);
    }

    public function getMocker(): InvocationMocker
    {
        return $this->invocationMocker;
    }
}
