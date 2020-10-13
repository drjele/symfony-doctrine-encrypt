<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Contract;

use Drjele\DoctrineEncrypt\Type\AbstractType;

interface EncryptorInterface
{
    public function getType(): ?AbstractType;

    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}
