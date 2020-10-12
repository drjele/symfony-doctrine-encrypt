<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt;

use Doctrine\DBAL\Types\Type;
use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;
use Drjele\DoctrineEncrypt\Type\EncryptedType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrjeleDoctrineEncryptBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        $this->registerType();
    }

    private function registerType(): void
    {
        /* this required because of how doctrine instantiates its types */

        if (!Type::hasType(EncryptedType::NAME)) {
            Type::addType(EncryptedType::NAME, EncryptedType::class);
        }

        /** @var EncryptorInterface $encryptorService */
        $encryptorService = $this->container->get(EncryptorInterface::class);

        /** @var EncryptedType $encryptedType */
        $encryptedType = Type::getType(EncryptedType::NAME);
        $encryptedType->setEncryptor($encryptorService);
    }
}
