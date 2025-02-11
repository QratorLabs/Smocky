<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionClassConstant;
use ReflectionException;
use RuntimeException;
use Throwable;

use function runkit7_constant_redefine;
use function sprintf;

use const RUNKIT7_ACC_PUBLIC;

class MockedClassConstant extends MockedGlobalConstant
{
    /** @var int */
    private $visibility = RUNKIT7_ACC_PUBLIC;

    /**
     * @param mixed                $newValue
     *
     * @throws RuntimeException
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $class, string $constant, $newValue)
    {
        try {
            $reflection = new ReflectionClassConstant($class, $constant);
        } catch (Throwable $exception) {
            throw new ReflectionException(
                sprintf('Failed to create reflection: %s', $exception->getMessage()),
                $exception->getCode(),
                $exception
            );
        }
        $this->visibility = $this->getVisibility($reflection);

        $this->name  = $class . '::' . $constant;
        $this->value = $reflection->getValue();

        if (!runkit7_constant_redefine($this->name, $newValue, $this->visibility)) {
            throw new RuntimeException(sprintf('Failed to redefine constant "%s"', $this->name));
        }
    }

    public function __destruct()
    {
        if (!runkit7_constant_redefine($this->name, $this->value, $this->visibility)) {
            throw new RuntimeException(sprintf('Failed to restore constant "%s"', $this->name));
        }
    }
}
