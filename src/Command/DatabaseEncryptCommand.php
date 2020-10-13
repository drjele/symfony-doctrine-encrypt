<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

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
            $this->error($e->getMessage());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }

    private function encrypt(EntityMetadataDto $entityMetadataDto): void
    {
    }
}
