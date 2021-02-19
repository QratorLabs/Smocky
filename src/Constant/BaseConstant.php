<?php

declare(strict_types=1);

namespace QratorLabs\Smocky\Constant;

use QratorLabs\Smocky\MockedEntity;

abstract class BaseConstant extends MockedEntity
{

    /** @var string */
    protected $name;

    /** @var mixed */
    protected $value;

    /**
     * Get original value of constant
     *
     * @return mixed
     */
    public function getValue()
    {
        return $this->value;
    }
}
