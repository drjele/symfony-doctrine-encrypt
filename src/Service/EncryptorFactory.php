<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Service;

use Doctrine\DBAL\Types\Type;
use Drjele\Doctrine\Encrypt\Contract\EncryptorInterface;
use Drjele\Doctrine\Encrypt\Exception\DuplicateEncryptorException;
use Drjele\Doctrine\Encrypt\Exception\EncryptorNotFoundException;
use Drjele\Doctrine\Encrypt\Exception\Exception;
use Drjele\Doctrine\Encrypt\Exception\TypeNotFoundException;
use Drjele\Doctrine\Encrypt\Type\AbstractType;

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
                if (\in_array($typeName, $this->typeNames, true)) {
                    throw new DuplicateEncryptorException(
                        \sprintf('multiple encryptors defined for type `%s`', $typeName)
                    );
                }

                $this->typeNames[] = $typeName;
            }

            $this->encryptors[\get_class($encryptor)] = $encryptor;
        }
    }

    /**
     * @return EncryptorInterface[]
     *
     * @internal
     */
    public function getEncryptors(): ?array
    {
        return $this->encryptors;
    }

    /** @internal */
    public function getTypeNames(): ?array
    {
        return $this->typeNames;
    }

    public function getEncryptor(string $encryptorClass): EncryptorInterface
    {
        if (!isset($this->encryptors[$encryptorClass])) {
            throw new EncryptorNotFoundException(\sprintf('no encryptor found for `%s`', $encryptorClass));
        }

        return $this->encryptors[$encryptorClass];
    }

    public function getEncryptorByType(string $typeName): EncryptorInterface
    {
        foreach ($this->encryptors as $encryptor) {
            if ($encryptor->getTypeName() === $typeName) {
                return $encryptor;
            }
        }

        throw new EncryptorNotFoundException(\sprintf('no encryptor found for type `%s`', $typeName));
    }

    public function getType(string $typeName): AbstractType
    {
        if (!\in_array($typeName, $this->typeNames, true)) {
            throw new TypeNotFoundException(\sprintf('no type found for `%s`', $typeName));
        }

        $type = Type::getType($typeName);

        if (($type instanceof AbstractType) === false) {
            throw new Exception(
                \sprintf('the encrypted type must extend `%s`', AbstractType::class)
            );
        }

        return $type;
    }
}
