<?php

declare(strict_types=1);

use Drjele\Doctrine\Encrypt\Command\AbstractDatabaseCommand;
use Drjele\Doctrine\Encrypt\Command\DatabaseDecryptCommand;
use Drjele\Doctrine\Encrypt\Command\DatabaseEncryptCommand;
use Drjele\Doctrine\Encrypt\Encryptor\AbstractEncryptor;
use Drjele\Doctrine\Encrypt\Encryptor\AES256Encryptor;
use Drjele\Doctrine\Encrypt\Encryptor\AES256FixedEncryptor;
use Drjele\Doctrine\Encrypt\Encryptor\FakeEncryptor;
use Drjele\Doctrine\Encrypt\Service\EncryptorFactory;
use Drjele\Doctrine\Encrypt\Service\EntityService;
use Symfony\Component\DependencyInjection\Argument\TaggedIteratorArgument;
use Symfony\Component\DependencyInjection\Loader\Configurator\ContainerConfigurator;
use Symfony\Component\DependencyInjection\Reference;

return function (ContainerConfigurator $containerConfigurator) {
    $services = $containerConfigurator->services();

    $encryptorTag = 'drjele.doctrine.encryptor';

    $services->set(AbstractEncryptor::class)
        ->abstract()
        ->arg('$salt', '%drjele_doctrine_encrypt.salt%');

    $services->set(AES256Encryptor::class)
        ->parent(AbstractEncryptor::class)
        ->tag($encryptorTag);

    $services->set(FakeEncryptor::class)
        ->tag($encryptorTag);

    $services->set(AES256FixedEncryptor::class)
        ->parent(AbstractEncryptor::class)
        ->tag($encryptorTag);

    $services->set(EncryptorFactory::class)
        ->public()
        ->arg('$encryptors', new TaggedIteratorArgument($encryptorTag));

    $services->set(AbstractDatabaseCommand::class)
        ->abstract()
        ->arg('$managerRegistry', new Reference('doctrine'))
        ->arg('$encryptorFactory', new Reference(EncryptorFactory::class))
        ->arg('$entityService', new Reference(EntityService::class));

    $services->set(DatabaseEncryptCommand::class)
        ->parent(AbstractDatabaseCommand::class)
        ->tag('console.command');

    $services->set(DatabaseDecryptCommand::class)
        ->parent(AbstractDatabaseCommand::class)
        ->tag('console.command');

    $services->set(EntityService::class)
        ->arg('$managerRegistry', new Reference('doctrine'))
        ->arg('$encryptorFactory', new Reference(EncryptorFactory::class));
};
