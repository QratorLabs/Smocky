<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionException;

use function runkit7_constant_add;
use function runkit7_constant_remove;
use function sprintf;

class UndefinedGlobalConstant extends MockedGlobalConstant
{
    /**
     * MockedConstant constructor.
     *
     * @param string $constantName
     *
     * @throws ReflectionException
     */
    public function __construct(string $constantName)
    {
        parent::__construct($constantName, null);
        if (!runkit7_constant_remove($constantName)) {
            throw new ReflectionException(sprintf('Failed to remove constant "%s"', $constantName));
        }
    }

    public function __destruct()
    {
        if (!runkit7_constant_add($this->name, $this->value)) {
            throw new ReflectionException(sprintf('Failed to restore constant "%s"', $this->name));
        }
    }
}
