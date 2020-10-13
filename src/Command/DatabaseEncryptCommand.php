<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DatabaseEncryptCommand extends AbstractDatabaseCommand
{
    protected static $defaultName = 'drjele:doctrine:database:encrypt';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $entitiesWithEncryption = $this->getEntitiesWithEncryption();
            if (!$entitiesWithEncryption) {
                $this->warning('No entites found to encrypt!');

                return static::SUCCESS;
            }

            foreach ($entitiesWithEncryption as $entityMetadataDto) {
                $this->encrypt($entityMetadataDto);
            }
        } catch (Throwable $e) {
            $this->error($e->__toString());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    private function encrypt(EntityMetadataDto $entityMetadataDto): void
    {
        $className = $entityMetadataDto->getClassMetadata()->getName();

        $this->io->section('[ENCRYPT] ' . $className);

        $fields = array_merge(
            $entityMetadataDto->getClassMetadata()->getIdentifier(),
            array_keys($entityMetadataDto->getEncryptionFields())
        );

        $em = $this->getManager();
        /** @var UnitOfWork $unitOfWork */
        $unitOfWork = $em->getUnitOfWork();

        /** @var EntityRepository $repository */
        $repository = $em->getRepository($className);

        $entities = $repository->createQueryBuilder('e')
            ->select('PARTIAL e.{' . implode(', ', $fields) . '}')
            ->getQuery()->getResult();

        $originalEntityData = [];
        foreach ($entityMetadataDto->getEncryptionFields() as $field => $type) {
            $originalEntityData[$field] = null;
        }

        foreach ($entities as $entity) {
            $unitOfWork->setOriginalEntityData($entity, $originalEntityData);

            $em->persist($entity);
        }

        $em->flush();
    }
}
