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
use Drjele\DoctrineEncrypt\Type\AbstractType;

class EncryptorFactory
{
    /** @var EncryptorInterface[] */
    private array $encryptors;

    public function __construct(iterable $encryptors)
    {
        /* @todo register only the configured encryptors */

        $this->encryptors = [];

        $types = [];

        /** @var EncryptorInterface $encryptor */
        foreach ($encryptors as $encryptor) {
            $typeName = $encryptor->getType()->getName();
            if (\in_array($typeName, $types)) {
                throw new DuplicateEncryptorException(sprintf('multiple encryptors defined for type "%s"', $typeName));
            }

            $this->encryptors[\get_class($encryptor)] = $encryptor;
        }
    }

    public function registerTypes(): void
    {
        foreach ($this->encryptors as $encryptor) {
            $type = $encryptor->getType();

            if (!$type) {
                continue;
            }

            if (!Type::hasType($type->getName())) {
                Type::addType($type->getName(), \get_class($type));
            }

            /** @var AbstractType $encryptedType */
            $encryptedType = Type::getType($type->getName());
            $encryptedType->setEncryptor($encryptor);
        }
    }

    public function get(string $encryptorClass): EncryptorInterface
    {
        if (!isset($this->encryptors[$encryptorClass])) {
            throw new EncryptorNotFoundException(sprintf('no encyptor found for "%s"', $encryptorClass));
        }

        return $this->encryptors[$encryptorClass];
    }

    public function getByType(string $typeName): EncryptorInterface
    {
        foreach ($this->encryptors as $encryptor) {
            if ($encryptor->getType()->getName() == $typeName) {
                return $encryptor;
            }
        }

        throw new EncryptorNotFoundException(sprintf('no encyptor found for type "%s"', $typeName));
    }

    public function getTypes(): array
    {
        $types = [];

        foreach ($this->encryptors as $encryptor) {
            $types[] = $encryptor->getType()->getName();
        }

        return $types;
    }
}
