<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Service;

use Doctrine\DBAL\Types\Type;
use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;
use Drjele\DoctrineEncrypt\Exception\DuplicateEncryptorException;
use Drjele\DoctrineEncrypt\Exception\EncryptorNotFoundException;
use Drjele\DoctrineEncrypt\Exception\TypeNotFoundException;
use Drjele\DoctrineEncrypt\Type\AbstractType;

class EncryptorFactory
{
    /** @var EncryptorInterface[] */
    private array $encryptors;
    private array $typeNames;

    public function __construct(iterable $encryptors)
    {
        /* @todo register only the configured encryptors */

        $this->encryptors = [];
        $this->typeNames = [];

        /** @var EncryptorInterface $encryptor */
        foreach ($encryptors as $encryptor) {
            $typeName = $encryptor->getTypeName();
            if ($typeName) {
                if (\in_array($typeName, $this->typeNames)) {
                    throw new DuplicateEncryptorException(
                        sprintf('Multiple encryptors defined for type "%s"', $typeName)
                    );
                }

                $this->typeNames[] = $typeName;
            }

            $this->encryptors[\get_class($encryptor)] = $encryptor;
        }
    }

    public function getTypeNames(): ?array
    {
        return $this->typeNames;
    }

    public function registerTypes(): void
    {
        foreach ($this->encryptors as $encryptor) {
            $typeClass = $encryptor->getTypeClass();

            if (!$typeClass) {
                continue;
            }

            $typeName = $encryptor->getTypeName();

            if (!Type::hasType($typeName)) {
                Type::addType($typeName, $typeClass);
            }

            /** @var AbstractType $encryptedType */
            $encryptedType = Type::getType($typeName);
            $encryptedType->setEncryptor($encryptor);
        }
    }

    public function get(string $encryptorClass): EncryptorInterface
    {
        if (!isset($this->encryptors[$encryptorClass])) {
            throw new EncryptorNotFoundException(sprintf('No encyptor found for "%s"', $encryptorClass));
        }

        return $this->encryptors[$encryptorClass];
    }

    public function getByType(string $typeName): EncryptorInterface
    {
        foreach ($this->encryptors as $encryptor) {
            if ($encryptor->getTypeName() == $typeName) {
                return $encryptor;
            }
        }

        throw new EncryptorNotFoundException(sprintf('No encyptor found for type "%s"', $typeName));
    }

    public function getType(string $typeName): AbstractType
    {
        if (!\in_array($typeName, $this->typeNames)) {
            throw new TypeNotFoundException(sprintf('No type found for type "%s"', $typeName));
        }

        return Type::getType($typeName);
    }
}
