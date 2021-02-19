<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\ClassMethod;

use Closure;
use ReflectionException;
use ReflectionMethod;

use function get_declared_classes;
use function get_parent_class;
use function is_subclass_of;
use function runkit7_method_add;
use function runkit7_method_remove;
use function runkit7_method_rename;

use const RUNKIT7_ACC_STATIC;

class MockedClassMethod extends UndefinedClassMethod
{

    /** @var Closure */
    protected $closure;

    /**
     * @var array<string, self>
     * @phpstan-var array<class-string, self>
     */
    protected $childrenMocks = [];

    /**
     * @param string               $class
     * @param string               $method
     * @param Closure|null         $closure
     *
     * @throws ReflectionException
     *
     * @phpstan-param class-string $class
     * @noinspection PhpUndefinedClassInspection
     * @noinspection PhpExpressionResultUnusedInspection
     */
    public function __construct(string $class, string $method, Closure $closure = null)
    {
        $this->closure =
            $closure ??
            static function (): void {
            };

        parent::__construct($class, $method);

        $reflection = new ReflectionMethod($this->class, $this->stashedName);
        $flags      = $reflection->isStatic() ? RUNKIT7_ACC_STATIC : 0;

        $flags |= $this->getVisibility($reflection);

        $closure = $this->closure;
        runkit7_method_add(
            $this->class,
            $this->method,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             */
            static function (...$args) use ($closure) {
                return $closure(...$args);
            },
            $flags
        );

        $this->stubChildrenMethods();
    }

    public function __destruct()
    {
        runkit7_method_remove($this->class, $this->method);
        runkit7_method_rename($this->class, $this->stashedName, $this->method);
    }

    /**
     * @throws ReflectionException
     */
    private function stubChildrenMethods(): void
    {
        $methodName = $this->method;
        foreach (get_declared_classes() as $className) {
            $parentClass = get_parent_class($className);
            if (
                $parentClass === false ||
                $className === $this->class ||
                !is_subclass_of($className, $this->class, true)
            ) {
                continue;
            }
            $reflection = new ReflectionMethod($className, $this->method);
            if ($reflection->getDeclaringClass()->name !== $this->class) {
                unset($reflection);
                continue;
            }
            $this->childrenMocks[$className] = new self(
                $className,
                $this->method,
                /**
                 * @param array<mixed> $args
                 *
                 * @return mixed
                 */
                static function (...$args) use ($parentClass, $methodName) {
                    return $parentClass::$methodName(...$args);
                }
            );
        }
    }
}
