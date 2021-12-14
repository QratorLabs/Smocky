<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Functions;

use ReflectionException;
use ReflectionFunction;

use function runkit7_function_rename;

class UndefinedFunction extends BaseFunction
{
    /**
     * @param string $function
     *
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
        runkit7_function_rename($this->getFullName(), $this->stashedName);
    }

    public function __destruct()
    {
        runkit7_function_rename($this->stashedName, $this->getFullName());
    }
}
