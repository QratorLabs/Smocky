<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Test\PhpUnit\Helpers;

use Generator;
use ReflectionException;
use ReflectionMethod;

/**
 * @internal
 */
class ClassWithMethods
{
    public static function publicStaticMethod(): string
    {
        return __FUNCTION__;
    }

    /**
     * @return Generator
     * @phpstan-return Generator<string, array{class-string, string}>
     * @throws ReflectionException
     */
    public static function getDataForTests(): Generator
    {
        foreach (
            [
                self::publicStaticMethod(),
                self::protectedStaticMethod(),
                self::privateStaticMethod(),
            ] as $methodName
        ) {
            $reflection = new ReflectionMethod(self::class, $methodName);
            $key        = 'public';
            if ($reflection->isProtected()) {
                $key = 'protected';
            } elseif ($reflection->isPrivate()) {
                $key = 'private';
            }
            yield $key . ' ' . $reflection->name => [static::class, $reflection->name];
        }
    }

    public function publicMethod(): string
    {
        return __FUNCTION__;
    }

    /** @noinspection PhpUnused */
    protected function protectedMethod(): string
    {
        return __FUNCTION__;
    }

    /** @noinspection PhpUnusedPrivateMethodInspection */
    private function privateMethod(): string
    {
        return __FUNCTION__;
    }

    protected static function protectedStaticMethod(): string
    {
        return __FUNCTION__;
    }

    private static function privateStaticMethod(): string
    {
        return __FUNCTION__;
    }
}
