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
use Symfony\Component\Console\Question\ConfirmationQuestion;

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
