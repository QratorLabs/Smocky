<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\ClassMethod;

use Closure;
use ReflectionException;
use ReflectionMethod;
use RuntimeException;

use function get_declared_classes;
use function get_parent_class;
use function is_subclass_of;
use function runkit7_method_add;
use function runkit7_method_remove;
use function runkit7_method_rename;

use const RUNKIT7_ACC_STATIC;

class MockedClassMethod extends UndefinedClassMethod
{
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
    public function __construct(string $class, string $method, ?Closure $closure = null)
    {
        parent::__construct($class, $method);

        $reflection = new ReflectionMethod($this->class, $this->stashedName);
        $flags      = $reflection->isStatic() ? RUNKIT7_ACC_STATIC : 0;

        $flags |= $this->getVisibility($reflection);

        $closureUse =
            $closure ??
            static function (): void {
            };

        runkit7_method_add(
            $this->class,
            $this->method,
            /**
             * @param array<mixed> $args
             *
             * @return mixed
             */
            static function (...$args) use ($closureUse) {
                return $closureUse(...$args);
            },
            $flags
        );

        $this->stubChildrenMethods();
    }

    /**
     * @param object|null $object
     * @param mixed       ...$args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function callOriginal($object, ...$args)
    {
        if (!($object instanceof $this->class)) {
            throw new RuntimeException('Object of "' . $this->class . '" expected.');
        }
        $ref = (new ReflectionMethod($this->class, $this->stashedName));
        if ($ref->isStatic()) {
            throw new RuntimeException(
                'Method "' . $this->class . '::' . $this->method . '" is static and cannot be called dynamically.'
            );
        }
        if (!$ref->isPublic()) {
            $ref->setAccessible(true);
        }

        return $ref->invoke($object, ...$args);
    }

    /**
     * @param mixed ...$args
     *
     * @return mixed
     * @throws ReflectionException
     */
    public function callOriginalStatic(...$args)
    {
        $ref = (new ReflectionMethod($this->class, $this->stashedName));
        if (!$ref->isStatic()) {
            throw new RuntimeException(
                'Method "' . $this->class . '::' . $this->method . '" is dynamic and cannot be called statically.'
            );
        }
        if (!$ref->isPublic()) {
            $ref->setAccessible(true);
        }

        return $ref->invoke(null, ...$args);
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
