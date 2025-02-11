<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionException;
use RuntimeException;

use function constant;
use function defined;
use function runkit7_constant_redefine;
use function sprintf;

use const RUNKIT7_ACC_PUBLIC;

class MockedGlobalConstant extends BaseConstant
{
    /**
     * MockedConstant constructor.
     *
     * @param string $constantName
     * @param mixed  $newValue
     *
     * @throws RuntimeException
     * @throws ReflectionException
     */
    public function __construct(string $constantName, $newValue)
    {
        if (!defined($constantName)) {
            throw new ReflectionException(sprintf('Constant "%s" is not defined', $constantName));
        }

        $this->name  = $constantName;
        $this->value = constant($constantName);

        if (!runkit7_constant_redefine($constantName, $newValue, RUNKIT7_ACC_PUBLIC)) {
            throw new RuntimeException(sprintf('Failed to redefine constant "%s"', $constantName));
        }
    }

    /**
     * @throws RuntimeException
     */
    public function __destruct()
    {
        if (!runkit7_constant_redefine($this->name, $this->value, RUNKIT7_ACC_PUBLIC)) {
            throw new RuntimeException(sprintf('Failed to restore constant "%s"', $this->name));
        }
    }
}
