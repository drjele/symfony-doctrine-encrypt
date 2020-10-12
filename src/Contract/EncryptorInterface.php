<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt\Contract;

interface EncryptorInterface
{
    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}
