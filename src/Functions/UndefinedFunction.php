<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Functions;

use ReflectionException;
use ReflectionFunction;
use RuntimeException;

use function runkit7_function_rename;
use function sprintf;

class UndefinedFunction extends BaseFunction
{
    /**
     * @param string $function
     *
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function __construct(string $function)
    {
        try {
            $reflection = new ReflectionFunction($function);
        } catch (ReflectionException $exception) {
            throw new ReflectionException(
                'Failed to create reflection: ' . $exception->getMessage(),
                $exception->getCode(),
                $exception
            );
        }

        $this->namespace = $reflection->inNamespace() ? $reflection->getNamespaceName() : null;
        $this->function  = $reflection->getShortName();

        $this->stashedName = $this->getStashedName($this->function);
        if (!runkit7_function_rename($this->getFullName(), $this->stashedName)) {
            throw new RuntimeException(sprintf('Failed to rename function "%s"', $this->getFullName()));
        }
    }

    public function __destruct()
    {
        if (!runkit7_function_rename($this->stashedName, $this->getFullName())) {
            throw new RuntimeException(sprintf('Failed to restore function "%s"', $this->getFullName()));
        }
    }
}
