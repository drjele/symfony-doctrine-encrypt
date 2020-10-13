<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use DateTime;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

abstract class AbstractCommand extends Command
{
    /** @todo add memory and time limits */
    protected InputInterface $input;
    protected OutputInterface $output;
    protected SymfonyStyle $io;

    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        $this->input = $input;
        $this->output = $output;

        $this->io = new SymfonyStyle($input, $output);

        $this->io->title(sprintf('<bg=blue>[%s]</> %s', (new DateTime())->format('Y-m-d'), $this->getName()));
    }

    protected function writeln(string $text): void
    {
        $this->io->writeln($this->format($text));
    }

    protected function error(string $text): void
    {
        $this->io->error($this->format($text));
    }

    protected function warning(string $text): void
    {
        $this->io->warning($this->format($text));
    }

    protected function success(string $text): void
    {
        $this->io->success($this->format($text));
    }

    private function format(string $text): string
    {
        return sprintf(
            '[%s][%s] %s',
            (new DateTime())->format('H:i:s'),
            $this->getMemoryUsage(),
            $text
        );
    }

    private function getMemoryUsage(): string
    {
        $size = memory_get_usage();
        $unit = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];

        $memory = round($size / pow(1024, ($i = floor(log($size, 1024)))), 2);

        return number_format($memory, 2, '.', '') . ' ' . $unit[$i];
    }
}
