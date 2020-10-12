<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Drjele\Utility\DoctrineEncrypt\Contract\EncryptorInterface;

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
        $platform->getEventManager()->getListeners('');

        return (null === $value) ? null : $this->encryptor->encrypt((string)$value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return (null === $value) ? null : $this->encryptor->decrypt((string)$value);
    }
}
