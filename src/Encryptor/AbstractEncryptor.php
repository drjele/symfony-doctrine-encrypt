<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Encryptor;

use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;

abstract class AbstractEncryptor implements EncryptorInterface
{
    protected const ENCRYPTION_MARKER = '<ENC>';

    protected string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }
}
