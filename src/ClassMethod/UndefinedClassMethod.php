<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\ClassMethod;

use ReflectionException;
use ReflectionMethod;

use function runkit7_method_rename;

class UndefinedClassMethod extends BaseClassMethod
{

    /**
     * @param string               $class
     * @param string               $method
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __construct(string $class, string $method)
    {
        try {
            new ReflectionMethod($class, $method);
        } catch (ReflectionException $exception) {
            throw new ReflectionException(
                'Failed to create reflection: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $this->class  = $class;
        $this->method = $method;

        $this->stashedName = $this->getStashedName($this->method);
        runkit7_method_rename($this->class, $this->method, $this->stashedName);
    }

    public function __destruct()
    {
        runkit7_method_rename($this->class, $this->stashedName, $this->method);
    }
}
