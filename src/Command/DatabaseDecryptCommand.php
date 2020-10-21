<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\UnitOfWork;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Drjele\DoctrineEncrypt\Encryptor\FakeEncryptor;
use Drjele\DoctrineEncrypt\Exception\StopException;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DatabaseDecryptCommand extends AbstractDatabaseCommand
{
    protected static $defaultName = 'drjele:doctrine:database:decrypt';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
            $entitiesWithEncryption = $this->entityService->getEntitiesWithEncryption();
            if (!$entitiesWithEncryption) {
                $this->warning('No entites found to decrypt!');

                throw new StopException();
            }

            $this->askForConfirmation($entitiesWithEncryption);

            $this->warning('Decrypting all the fields can take up to several minutes depending on the database size.');

            foreach ($entitiesWithEncryption as $entityMetadataDto) {
                $this->encrypt($entityMetadataDto);
            }

            $this->success('Decryption finished.');
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

        $this->io->section('[DECRYPT]' . $className);

        $fields = array_merge(
            $entityMetadataDto->getClassMetadata()->getIdentifier(),
            array_keys($entityMetadataDto->getEncryptionFields())
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
                ->select('PARTIAL e.{' . implode(', ', $fields) . '}')
                ->setMaxResults(50)
                ->setFirstResult($i)
                ->getQuery()->getResult();

            $originalEntityData = $this->getOriginalEntityData($entityMetadataDto);

            $resetedEncryptors = $this->resetEncryptors($entityMetadataDto->getEncryptionFields());

            foreach ($entities as $entity) {
                ++$i;

                $unitOfWork->setOriginalEntityData($entity, $originalEntityData);

                $em->persist($entity);

                $progressBar->advance();
            }

            $em->flush();

            $this->restoreEncryptors($resetedEncryptors);

            $em->clear();
            gc_collect_cycles();
        } while ($entities);

        $progressBar->finish();

        $this->writeln('');
    }

    private function resetEncryptors(array $encryptionFields): array
    {
        $resetedEncryptors = [];

        foreach ($encryptionFields as $field => $typeName) {
            $type = $this->encryptorFactory->getType($typeName);

            $resetedEncryptors[$typeName] = $type->getEncryptor();

            $type->setEncryptor(
                $this->encryptorFactory->getEncryptor(FakeEncryptor::class)
            );
        }

        return $resetedEncryptors;
    }

    private function restoreEncryptors(array $resetedEncryptors): void
    {
        foreach ($resetedEncryptors as $typeName => $encryptor) {
            $type = $this->encryptorFactory->getType($typeName);

            $type->setEncryptor($encryptor);
        }
    }
}
