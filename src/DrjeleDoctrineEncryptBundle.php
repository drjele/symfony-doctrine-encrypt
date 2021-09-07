<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt;

use Doctrine\DBAL\Types\Type;
use Drjele\Doctrine\Encrypt\Exception\TypeNotFoundException;
use Drjele\Doctrine\Encrypt\Service\EncryptorFactory;
use Drjele\Doctrine\Encrypt\Type\AbstractType;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrjeleDoctrineEncryptBundle extends Bundle
{
    public function boot(): void
    {
        parent::boot();

        $this->registerTypes();
    }

    private function registerTypes(): void
    {
        /* this required because of how doctrine instantiates its types */

        /** @var EncryptorFactory $encryptorFactory */
        $encryptorFactory = $this->container->get(EncryptorFactory::class);

        $enabledTypes = $this->container->getParameter('drjele_doctrine_encrypt.enabled_types');
        $diff = \array_diff($enabledTypes, $encryptorFactory->getTypeNames());
        if ($diff) {
            throw new TypeNotFoundException(\sprintf('No type found for "%s"', \implode(', ', $diff)));
        }

        foreach ($encryptorFactory->getEncryptors() as $encryptor) {
            $typeClass = $encryptor->getTypeClass();

            if (!$typeClass) {
                continue;
            }

            $typeName = $encryptor->getTypeName();

            if ($enabledTypes && !\in_array($typeName, $enabledTypes, true)) {
                continue;
            }

            if (!Type::hasType($typeName)) {
                Type::addType($typeName, $typeClass);
            }

            /** @var AbstractType $encryptedType */
            $encryptedType = Type::getType($typeName);
            $encryptedType->setEncryptor($encryptor);
        }
    }
}
