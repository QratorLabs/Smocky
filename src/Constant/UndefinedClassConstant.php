<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionClassConstant;
use ReflectionException;
use Throwable;

use function runkit7_constant_add;
use function runkit7_constant_remove;

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
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpMissingParentConstructorInspection
     * @noinspection PhpExpressionResultUnusedInspection
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
        runkit7_constant_remove($this->name);
    }

    /**
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __destruct()
    {
        runkit7_constant_add($this->name, $this->value, $this->visibility);
    }
}
