<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\ClassMethod;

use QratorLabs\Smocky\MockedEntity;

abstract class BaseClassMethod extends MockedEntity
{
    /**
     * @var string
     * @phpstan-var class-string
     */
    protected $class;

    /** @var string */
    protected $method;

    /** @var string */
    protected $stashedName;

    /**
     * BaseClassMethod constructor.
     *
     * @param string               $class
     * @param string               $method
     *
     * @phpstan-param class-string $class
     */
    abstract public function __construct(string $class, string $method);

    /**
     * @return string
     */
    public function getMethod(): string
    {
        return $this->method;
    }

    /**
     * @return string
     * @phpstan-return  class-string
     */
    public function getClass(): string
    {
        return $this->class;
    }
}
