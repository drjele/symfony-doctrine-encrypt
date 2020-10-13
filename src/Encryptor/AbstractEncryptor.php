<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Encryptor;

use Drjele\DoctrineEncrypt\Type\AbstractType;

abstract class AbstractEncryptor
{
    protected const ENCRYPTION_MARKER = '<ENC>';

    protected string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    final public function getTypeName(): ?string
    {
        /** @var AbstractType $type */
        $type = $this->getTypeClass();

        return $type::getFullName();
    }
}
