<?php

declare(strict_types=1);

/*
 * Copyright (c) Constantin Adrian Jeledintan
 */

namespace Drjele\Utility\DoctrineEncrypt\DependencyInjection;

use Drjele\Utility\DoctrineEncrypt\Contract\EncryptorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DrjeleUtilityDoctrineEncryptExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $container)
    {
        $loader = new YamlFileLoader(
            $container,
            new FileLocator(__DIR__ . '/../Resources/config')
        );
        $loader->load('services.yaml');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $container->setParameter('drjele_utility_doctrine_encrypt.salt', $config['salt']);
        $container->setParameter(
            'drjele_utility_doctrine_encrypt.encryptor_service_class',
            $config['encryptor_service_class']
        );

        $alias = $container->setAlias(EncryptorInterface::class, $config['encryptor_service_class']);
        $alias->setPublic(true)
            ->setPrivate(false);
    }
}
