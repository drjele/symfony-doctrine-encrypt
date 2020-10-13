<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Throwable;

class DatabaseDecryptCommand extends AbstractDatabaseCommand
{
    protected static $defaultName = 'drjele:doctrine:database:decrypt';

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        try {
        } catch (Throwable $e) {
            $this->error($e->getMessage());

            return static::FAILURE;
        }

        return static::SUCCESS;
    }
}