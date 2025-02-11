<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionClassConstant;
use ReflectionException;
use RuntimeException;
use Throwable;

use function runkit7_constant_add;
use function runkit7_constant_remove;
use function sprintf;

class UndefinedClassConstant extends UndefinedGlobalConstant
{
    /** @var int */
    private $visibility;

    /**
     * MockedConstant constructor.
     *
     * @param string               $class
     * @param string               $constantName
     *
     * @throws RuntimeException
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpMissingParentConstructorInspection
     */
    public function __construct(string $class, string $constantName)
    {
        try {
            $reflection = new ReflectionClassConstant($class, $constantName);
        } catch (Throwable $exception) {
            throw new ReflectionException(
                'Failed to create reflection: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }
        $this->name       = $class . '::' . $constantName;
        $this->value      = $reflection->getValue();
        $this->visibility = $this->getVisibility($reflection);
        if (!runkit7_constant_remove($this->name)) {
            throw new RuntimeException(sprintf('Failed to remove constant "%s"', $this->name));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function __destruct()
    {
        if (!runkit7_constant_add($this->name, $this->value, $this->visibility)) {
            throw new RuntimeException(sprintf('Failed to restore constant "%s"', $this->name));
        }
    }
}
