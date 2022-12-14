<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;
use Drjele\Doctrine\Encrypt\Contract\EncryptorInterface;
use Drjele\Doctrine\Encrypt\Exception\Exception;

abstract class AbstractType extends StringType
{
    private EncryptorInterface $encryptor;

    abstract protected static function getShortName(): string;

    final public static function getFullName(): string
    {
        return 'encrypted' . static::getShortName();
    }

    final public function getEncryptor(): ?EncryptorInterface
    {
        return $this->encryptor;
    }

    final public function setEncryptor(EncryptorInterface $encryptor): self
    {
        $this->encryptor = $encryptor;

        return $this;
    }

    final public function getName(): string
    {
        return static::getFullName();
    }

    final public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $this->validate();

        return (null === $value) ? null : $this->encryptor->encrypt((string)$value);
    }

    final public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        $this->validate();

        return (null === $value) ? null : $this->encryptor->decrypt((string)$value);
    }

    private function validate(): void
    {
        if (false === isset($this->encryptor)) {
            throw new Exception('the encryptor was not set');
        }
    }
}
