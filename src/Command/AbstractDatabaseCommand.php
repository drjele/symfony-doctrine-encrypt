<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Drjele\DoctrineEncrypt\Service\EncryptorFactory;

abstract class AbstractDatabaseCommand extends AbstractCommand
{
    protected ManagerRegistry $managerRegistry;
    protected EncryptorFactory $encryptorFactory;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EncryptorFactory $encryptorFactory
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->encryptorFactory = $encryptorFactory;

        parent::__construct();
    }

    protected function getManager(): ObjectManager
    {
        /* @todo add param for manager name */
        return $this->managerRegistry->getManager();
    }

    /** @return EntityMetadataDto[] */
    protected function getEntitiesWithEncryption(): array
    {
        $encryptedTypes = $this->encryptorFactory->getTypeNames();
        $entites = [];

        foreach ($this->getManager()->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $encryptionFields = [];

            foreach ($classMetadata->getFieldNames() as $fieldName) {
                $type = $classMetadata->getTypeOfField($fieldName);

                if (\in_array($type, $encryptedTypes)) {
                    $encryptionFields[$fieldName] = $type;
                }
            }

            if ($encryptionFields) {
                $entites[] = new EntityMetadataDto($classMetadata, $encryptionFields);
            }
        }

        return $entites;
    }
}
