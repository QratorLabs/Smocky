<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionException;

use function constant;
use function defined;
use function runkit7_constant_redefine;

use const RUNKIT7_ACC_PUBLIC;

class MockedGlobalConstant extends BaseConstant
{
    /**
     * MockedConstant constructor.
     *
     * @param string $constantName
     * @param mixed  $newValue
     *
     * @throws ReflectionException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __construct(string $constantName, $newValue)
    {
        if (!defined($constantName)) {
            throw new ReflectionException("Constant $constantName is not defined");
        }

        $this->name  = $constantName;
        $this->value = constant($constantName);

        runkit7_constant_redefine($constantName, $newValue, RUNKIT7_ACC_PUBLIC);
    }

    /**
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __destruct()
    {
        runkit7_constant_redefine($this->name, $this->value, RUNKIT7_ACC_PUBLIC);
    }
}
