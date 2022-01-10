<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Drjele\Doctrine\Encrypt\Contract\EncryptorInterface;
use Drjele\Doctrine\Encrypt\Dto\EntityMetadataDto;
use Drjele\Doctrine\Encrypt\Exception\FieldNotEncryptedException;

class EntityService
{
    private ManagerRegistry $managerRegistry;
    private EncryptorFactory $encryptorFactory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EncryptorFactory $encryptorFactory
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->encryptorFactory = $encryptorFactory;
    }

    public function getEncryptor(
        string $class,
        string $field,
        string $managerName = null
    ): EncryptorInterface {
        $encryptionFields = $this->getEncryptedFields($class, $managerName);

        if (!isset($encryptionFields[$field])) {
            throw new FieldNotEncryptedException(
                \sprintf('field %s::%s has no encryption defined', $class, $field)
            );
        }

        return $this->encryptorFactory->getEncryptorByType($encryptionFields[$field]);
    }

    public function hasEncryptor(
        string $class,
        string $field,
        string $managerName = null
    ): bool {
        $encryptionFields = $this->getEncryptedFields($class, $managerName);

        return isset($encryptionFields[$field]);
    }

    public function encrypt(
        string $data,
        string $class,
        string $field,
        string $managerName = null
    ): string {
        $encryptor = $this->getEncryptor($class, $field, $managerName);

        return $encryptor->encrypt($data);
    }

    public function decrypt(
        string $encryptedData,
        string $class,
        string $field,
        string $managerName = null
    ): string {
        $encryptor = $this->getEncryptor($class, $field, $managerName);

        return $encryptor->decrypt($encryptedData);
    }

    /** @return EntityMetadataDto[] */
    public function getEntitiesWithEncryption(
        string $manager = null
    ): array {
        $entities = [];

        $manager = $this->managerRegistry->getManager($manager);

        foreach ($manager->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $encryptionFields = $this->getFieldsForClassMetadata($classMetadata);

            if ($encryptionFields) {
                $entities[$classMetadata->getName()] = new EntityMetadataDto($classMetadata, $encryptionFields);
            }
        }

        return $entities;
    }

    private function getEncryptedFields(
        string $class,
        string $managerName = null
    ): array {
        $manager = $this->managerRegistry->getManager($managerName);

        $classMetadata = $manager->getMetadataFactory()->getMetadataFor($class);

        return $this->getFieldsForClassMetadata($classMetadata);
    }

    private function getFieldsForClassMetadata(
        ClassMetadata $classMetadata
    ): array {
        $encryptedTypes = $this->encryptorFactory->getTypeNames();

        $encryptionFields = [];

        foreach ($classMetadata->getFieldNames() as $fieldName) {
            $type = $classMetadata->getTypeOfField($fieldName);

            if (\in_array($type, $encryptedTypes, true)) {
                $encryptionFields[$fieldName] = $type;
            }
        }

        return $encryptionFields;
    }
}
