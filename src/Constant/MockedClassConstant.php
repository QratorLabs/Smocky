<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionClassConstant;
use ReflectionException;
use Throwable;

use function runkit7_constant_redefine;

use const RUNKIT7_ACC_PUBLIC;

class MockedClassConstant extends MockedGlobalConstant
{
    /** @var int */
    private $visibility = RUNKIT7_ACC_PUBLIC;

    /**
     * @param string               $class
     * @param string               $constant
     * @param mixed                $newValue
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __construct(string $class, string $constant, $newValue)
    {
        try {
            $reflection = new ReflectionClassConstant($class, $constant);
        } catch (Throwable $exception) {
            throw new ReflectionException(
                'Failed to create reflection: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        $this->visibility = $this->getVisibility($reflection);

        $this->name  = $class . '::' . $constant;
        $this->value = $reflection->getValue();

        runkit7_constant_redefine($this->name, $newValue, $this->visibility);
    }

    /**
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __destruct()
    {
        runkit7_constant_redefine($this->name, $this->value, $this->visibility);
    }
}
