<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Helpers;

use Generator;
use ReflectionClassConstant;

/**
 * @internal
 */
class ClassWithConstants
{
    public const    CONST_PUBLIC    = 'CONST_PUBLIC';
    protected const CONST_PROTECTED = 'CONST_PROTECTED';
    private const   CONST_PRIVATE   = 'CONST_PRIVATE';

    /**
     * @return Generator
     * @phpstan-return Generator<string, array{class-string, string}>
     */
    public static function getDataForTests(): Generator
    {
        foreach (
            [
                self::CONST_PUBLIC,
                self::CONST_PROTECTED,
                self::CONST_PRIVATE,
            ] as $constantName
        ) {
            $reflection = new ReflectionClassConstant(static::class, $constantName);
            $key        = 'public';
            if ($reflection->isProtected()) {
                $key = 'protected';
            } elseif ($reflection->isPrivate()) {
                $key = 'private';
            }
            yield $key => [static::class, $constantName];
        }
    }
}
