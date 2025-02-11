<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\ClassMethod;

use ReflectionException;
use ReflectionMethod;
use RuntimeException;

use function runkit7_method_rename;
use function sprintf;

class UndefinedClassMethod extends BaseClassMethod
{
    /**
     * @param string               $class
     * @param string               $method
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
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
        if (!runkit7_method_rename($this->class, $this->method, $this->stashedName)) {
            throw new ReflectionException(
                sprintf('Failed to move method "%s::%s" to %s', $this->class, $this->method, $this->stashedName)
            );
        }
    }

    /**
     * @throws RuntimeException
     */
    public function __destruct()
    {
        if (!runkit7_method_rename($this->class, $this->stashedName, $this->method)) {
            throw new RuntimeException(
                sprintf('Failed to restore method "%s::%s"', $this->class, $this->method)
            );
        }
    }
}
