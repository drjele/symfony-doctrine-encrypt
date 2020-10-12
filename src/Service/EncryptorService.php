<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt\Service;

use Drjele\Utility\DoctrineEncrypt\Contract\EncryptorInterface;

class EncryptorService implements EncryptorInterface
{
    private string $salt;

    public function __construct(string $salt)
    {
        $this->salt = $salt;
    }

    public function encrypt(string $data): string
    {
        return $data;
    }

    public function decrypt(string $data): string
    {
        return substr($data, 0, -4);
    }
}
