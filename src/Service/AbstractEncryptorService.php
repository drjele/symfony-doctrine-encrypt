<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Service;

use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;

abstract class AbstractEncryptorService implements EncryptorInterface
{
    protected string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }
}
