<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Drjele\DoctrineEncrypt\Type\EncryptedType;

abstract class AbstractDatabaseCommand extends AbstractCommand
{
    protected ManagerRegistry $managerRegistry;

    public function __construct(ManagerRegistry $managerRegistry)
    {
        $this->managerRegistry = $managerRegistry;

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
        $entites = [];

        foreach ($this->getManager()->getMetadataFactory()->getAllMetadata() as $classMetadata) {
            $encryptionFields = [];

            foreach ($classMetadata->getFieldNames() as $fieldName) {
                $type = $classMetadata->getTypeOfField($fieldName);

                if (EncryptedType::NAME === $type) {
                    $encryptionFields[] = $fieldName;
                }
            }

            if ($encryptionFields) {
                $entites[] = new EntityMetadataDto($classMetadata, $encryptionFields);
            }
        }

        return $entites;
    }
}
