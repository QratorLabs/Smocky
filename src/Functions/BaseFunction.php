<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Functions;

use QratorLabs\Smocky\MockedEntity;

abstract class BaseFunction extends MockedEntity
{
    /**
     * @var ?string
     */
    protected $namespace = null;

    /** @var string */
    protected $function;

    /** @var string */
    protected $stashedName;

    /**
     * BaseFunction constructor.
     *
     * @param string $function
     */
    abstract public function __construct(string $function);

    public function getShortName(): string
    {
        return $this->function;
    }

    public function getFullName(): string
    {
        return $this->namespace === null ? $this->function : ($this->namespace . '\\' . $this->function);
    }
}
