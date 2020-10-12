<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt;

use Doctrine\DBAL\Types\Type;
use Drjele\Utility\DoctrineEncrypt\Contract\EncryptorInterface;
use Drjele\Utility\DoctrineEncrypt\Type\EncryptedType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrjeleUtilityDoctrineEncryptBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        if (!Type::hasType(EncryptedType::NAME)) {
            Type::addType(EncryptedType::NAME, EncryptedType::class);
        }

        /** @var EncryptorInterface $encryptorService */
        $encryptorService = $this->container->get(
            $this->container->getParameter('drjele_utility_doctrine_encrypt.encryptor_service')
        );

        /** @var EncryptedType $encryptedType */
        $encryptedType = Type::getType(EncryptedType::NAME);
        $encryptedType->setEncryptor($encryptorService);
    }
}
