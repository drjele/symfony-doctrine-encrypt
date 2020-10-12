<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;
use Drjele\DoctrineEncrypt\Exception\Exception;

class EncryptedType extends StringType
{
    const NAME = 'encrypted';

    private EncryptorInterface $encryptor;

    public function setEncryptor(EncryptorInterface $encryptor): self
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    public function getName()
    {
        return static::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $this->validate();

        return (null === $value) ? null : $this->encryptor->encrypt((string)$value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        $this->validate();

        return (null === $value) ? null : $this->encryptor->decrypt((string)$value);
    }

    private function validate(): void
    {
        if (false == isset($this->encryptor)) {
            throw new Exception('The encryptor was not set!');
        }
    }
}
