<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Drjele\DoctrineEncrypt\Exception\FieldNotEncryptedException;

class EntityService
{
    protected ManagerRegistry $managerRegistry;
    private EncryptorFactory $encryptorFactory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EncryptorFactory $encryptorFactory
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->encryptorFactory = $encryptorFactory;
    }

    public function getEncryptor(string $class, string $field, string $manager = null): EncryptorInterface
    {
        $manager = $this->managerRegistry->getManager($manager);

        $classMetadata = $manager->getMetadataFactory()->getMetadataFor($class);

        $encryptionFields = $this->getEncryptionFields($classMetadata);

        if (!isset($encryptionFields[$field])) {
            throw new FieldNotEncryptedException(
                sprintf('Field %s::%s has no encryption defined', $class, $field)
            );
        }

        return $this->encryptorFactory->getByType($encryptionFields[$field]);
    }

    /** @return EntityMetadataDto[] */
    public function getEntitiesWithEncryption(string $manager = null): array
    {
        $entites = [];

        $manager = $this->managerRegistry->getManager($manager);

        foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $encryptionFields = $this->getEncryptionFields($classMetadata);

            if ($encryptionFields) {
                $entites[$classMetadata->getName()] = new EntityMetadataDto($classMetadata, $encryptionFields);
            }
        }

        return $entites;
    }

    private function getEncryptionFields(ClassMetadata $classMetadata): array
    {
        $encryptedTypes = $this->encryptorFactory->getTypeNames();

        $encryptionFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $type = $classMetadata->getTypeOfField($fieldName);

            if (\in_array($type, $encryptedTypes)) {
                $encryptionFields[$fieldName] = $type;
            }
        }

        return $encryptionFields;
    }
}
