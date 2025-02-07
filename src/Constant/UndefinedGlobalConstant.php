<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use ReflectionException;

use function assert;
use function runkit7_constant_add;
use function runkit7_constant_remove;

use const RUNKIT7_ACC_PUBLIC;

class UndefinedGlobalConstant extends MockedGlobalConstant
{
    /**
     * MockedConstant constructor.
     *
     * @param string $constantName
     *
     * @throws ReflectionException
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __construct(string $constantName)
    {
        parent::__construct($constantName, null);
        assert(runkit7_constant_remove($constantName));
    }

    /**
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __destruct()
    {
        assert(runkit7_constant_add($this->name, $this->value, RUNKIT7_ACC_PUBLIC));
    }
}
