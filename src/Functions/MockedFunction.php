<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Functions;

use Closure;
use ReflectionException;

use function runkit7_function_add;
use function runkit7_function_remove;
use function runkit7_function_rename;

class MockedFunction extends UndefinedFunction
{
    /** @var Closure */
    protected $closure;

    /**
     * @param string $function
     * @param Closure|null $closure
     *
     * @throws ReflectionException
     */
    public function __construct(string $function, ?Closure $closure = null)
    {
        $this->closure =
            $closure ??
            static function (): void {
            };

        parent::__construct($function);
        $closure = $this->closure;
        $tmpName = $this->getStashedName($this->function);
        runkit7_function_add(
            $tmpName,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             */
            static function (...$args) use ($closure) {
                return $closure(...$args);
            }
        );
        runkit7_function_rename($tmpName, $this->getFullName());
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     */
    public function callOriginal(...$args)
    {
        /** @phpstan-ignore-next-line */
        return ($this->stashedName)(...$args);
    }

    public function __destruct()
    {
        runkit7_function_remove($this->getFullName());
        parent::__destruct();
    }
}
