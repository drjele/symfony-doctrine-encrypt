<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Command;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use Drjele\Doctrine\Encrypt\Dto\EntityMetadataDto;
use Drjele\Doctrine\Encrypt\Exception\StopException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DatabaseEncryptCommand extends AbstractDatabaseCommand
{
    public const NAME = 'drjele:doctrine:database:encrypt';

    protected static $defaultName = self::NAME;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $entitiesWithEncryption = $this->entityService->getEntitiesWithEncryption($this->getManagerName());
            if (!$entitiesWithEncryption) {
                $this->warning('No entities found to encrypt!');

                throw new StopException();
            }

            $this->askForConfirmation($entitiesWithEncryption);

            $this->warning('Encrypting all the fields can take up to several minutes depending on the database size.');

            foreach ($entitiesWithEncryption as $entityMetadataDto) {
                $this->encrypt($entityMetadataDto);
            }

            $this->success('Encryption finished.');
        } catch (StopException $t) {
            /* ignore */
        } catch (Throwable $t) {
            $this->error($t->__toString());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    private function encrypt(EntityMetadataDto $entityMetadataDto): void
    {
        $className = $entityMetadataDto->getClassMetadata()->getName();

        $this->style->section('[ENCRYPT] ' . $className);

        $fields = \array_merge(
            $entityMetadataDto->getClassMetadata()->getIdentifier(),
            \array_keys($entityMetadataDto->getEncryptionFields())
        );

        $em = $this->getManager();
        /** @var UnitOfWork $unitOfWork */
        $unitOfWork = $em->getUnitOfWork();

        /** @var EntityRepository $repository */
        $repository = $em->getRepository($className);

        $total = $repository->createQueryBuilder('e')
            ->select('COUNT(e)')
            ->getQuery()->getSingleScalarResult();

        $progressBar = new ProgressBar($this->output, (int)$total);
        $i = 0;

        do {
            $entities = $repository->createQueryBuilder('e')
                ->select('PARTIAL e.{' . \implode(', ', $fields) . '}')
                ->setMaxResults(50)
                ->setFirstResult($i)
                ->getQuery()->getResult();

            $originalEntityData = $this->getOriginalEntityData($entityMetadataDto);

            foreach ($entities as $entity) {
                ++$i;

                $unitOfWork->setOriginalEntityData($entity, $originalEntityData);

                $em->persist($entity);

                $progressBar->advance();
            }

            $em->flush();

            $em->clear();
            \gc_collect_cycles();
        } while ($entities);

        $progressBar->finish();

        $this->writeln('');
    }
}
