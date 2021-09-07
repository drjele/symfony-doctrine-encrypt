<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Contract;

interface EncryptorInterface
{
    public function getTypeClass(): ?string;

    public function getTypeName(): ?string;

    public function encrypt(string $data): string;

    public function decrypt(string $data): string;
}
