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
use function sprintf;

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

        /**
         * @param array<mixed> $args
         *
         * @return mixed
         */
        $closureWrapper = static function (...$args) use ($closureUse) {
            return $closureUse(...$args);
        };
        if (!runkit7_method_add($this->class, $this->method, $closureWrapper, $flags)) {
            throw new RuntimeException(sprintf('Failed to mock method "%s::%s".', $this->class, $this->method));
        }

        $this->stubChildrenMethods();
    }

    public function __destruct()
    {
        if (
            !runkit7_method_remove($this->class, $this->method)
            || !runkit7_method_rename($this->class, $this->stashedName, $this->method)
        ) {
            throw new RuntimeException(sprintf('Failed to restore method "%s::%s".', $this->class, $this->method));
        }
    }

    /**
     * @param object|null $object
     * @param mixed       ...$args
     *
     * @return mixed
     * @throws ReflectionException
     * @throws RuntimeException
     */
    public function callOriginal($object, ...$args)
    {
        if (!($object instanceof $this->class)) {
            throw new RuntimeException(sprintf('Object of "%s" expected.', $this->class));
        }
        $ref = (new ReflectionMethod($this->class, $this->stashedName));
        if ($ref->isStatic()) {
            throw new RuntimeException(
                sprintf('Method "%s::%s" is static and cannot be called dynamically.', $this->class, $this->method)
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
                sprintf('Method "%s::%s" is dynamic and cannot be called statically.', $this->class, $this->method)
            );
        }
        if (!$ref->isPublic()) {
            $ref->setAccessible(true);
        }

        return $ref->invoke(null, ...$args);
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
                !is_subclass_of($className, $this->class)
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
                 * @noinspection PhpPluralMixedCanBeReplacedWithArrayInspection
                 */
                static function (...$args) use ($parentClass, $methodName) {
                    return $parentClass::$methodName(...$args);
                }
            );
        }
    }
}
