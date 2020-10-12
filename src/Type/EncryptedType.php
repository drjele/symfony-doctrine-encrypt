<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt\Type;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class EncryptedType extends StringType
{
    public function getName()
    {
        return 'encrypted';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        $platform->getEventManager()->getListeners('');

        return (null === $value) ? null : $this->encrypt((string)$value);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?string
    {
        return (null === $value) ? null : $this->decrypt((string)$value);
    }

    private function encrypt(string $value): string
    {
        return $value . '-ptc';
    }

    private function decrypt(string $encryptedValue): string
    {
        return substr($encryptedValue, 0, -4);
    }
}
