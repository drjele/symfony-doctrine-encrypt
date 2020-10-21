<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Command;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\ObjectManager;
use Drjele\DoctrineEncrypt\Dto\EntityMetadataDto;
use Drjele\DoctrineEncrypt\Exception\StopException;
use Drjele\DoctrineEncrypt\Service\EncryptorFactory;
use Drjele\DoctrineEncrypt\Service\EntityService;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Question\ConfirmationQuestion;

abstract class AbstractDatabaseCommand extends AbstractCommand
{
    protected const OPTION_MANAGER = 'namager';

    protected ManagerRegistry $managerRegistry;
    protected EncryptorFactory $encryptorFactory;
    protected EntityService $entityService;

    public function __construct(
        ManagerRegistry $managerRegistry,
        EncryptorFactory $encryptorFactory,
        EntityService $entityService
    ) {
        $this->managerRegistry = $managerRegistry;
        $this->encryptorFactory = $encryptorFactory;
        $this->entityService = $entityService;

        parent::__construct();
    }

    protected function configure()
    {
        parent::configure();

        $this->addOption(static::OPTION_MANAGER, null, InputOption::VALUE_OPTIONAL, 'the entity manager for witch to run the command');
    }

    protected function getManagerName(): ?string
    {
        return $this->input->getOption(static::OPTION_MANAGER);
    }

    protected function getManager(): ObjectManager
    {
        $managerName = $this->getManagerName();

        return $this->managerRegistry->getManager($managerName);
    }

    protected function getOriginalEntityData(EntityMetadataDto $entityMetadataDto): array
    {
        $originalEntityData = [];

        foreach ($entityMetadataDto->getEncryptionFields() as $field => $type) {
            $originalEntityData[$field] = null;
        }

        return $originalEntityData;
    }

    protected function askForConfirmation(array $entitiesWithEncryption): void
    {
        $confirmationQuestion = new ConfirmationQuestion(
            $this->getQuestionText(
                [
                    \count($entitiesWithEncryption) . ' entities found which are containing properties with encryption types.',
                    'Wrong settings can mess up your data and it will be unrecoverable.',
                    'I advise you to make a backup.',
                    'Continue with this action? (y/yes)',
                ]
            ),
            false
        );

        $question = $this->getHelper('question');
        if (!$question->ask($this->input, $this->output, $confirmationQuestion)) {
            throw new StopException();
        }
    }

    private function getQuestionText(array $questionParts): string
    {
        /** @todo allow styles */
        $maxLength = 0;
        foreach ($questionParts as $questionPart) {
            $maxLength = max(\strlen($questionPart), $maxLength);
        }

        $indent = str_repeat(' ', 4);

        foreach ($questionParts as &$questionPart) {
            $questionPart = $indent . str_pad($questionPart, $maxLength, ' ');
        }
        unset($questionPart);

        $questionText = '<question>' . implode(PHP_EOL, $questionParts) . '</question>: ';

        return $questionText;
    }
}
