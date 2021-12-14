<?php

declare(strict_types=1);

namespace QratorLabs\Smocky;

use ReflectionClassConstant;
use ReflectionMethod;

use function strtr;
use function uniqid;

use const RUNKIT7_ACC_PRIVATE;
use const RUNKIT7_ACC_PROTECTED;
use const RUNKIT7_ACC_PUBLIC;

abstract class MockedEntity
{
    /**
     * All cleanups should be done here
     *
     * Goal - revert anything to it's original places
     *
     * @return void
     */
    abstract public function __destruct();

    /**
     * @param string $basename
     * @param string $prefix
     *
     * @return string
     */
    protected function getStashedName(string $basename, string $prefix = ''): string
    {
        return '__smocky_stashed_' . $prefix . '__' . strtr(uniqid('', true), '.', '_') . '__' . $basename;
    }

    /**
     * @param ReflectionMethod|ReflectionClassConstant $reflection
     *
     * @return int
     */
    protected function getVisibility($reflection): int
    {
        switch (true) {
            case $reflection->isProtected():
                $visibility = RUNKIT7_ACC_PROTECTED;
                break;
            case $reflection->isPrivate():
                $visibility = RUNKIT7_ACC_PRIVATE;
                break;
            default:
                $visibility = RUNKIT7_ACC_PUBLIC;
        }

        return $visibility;
    }
}
