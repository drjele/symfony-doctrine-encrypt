<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\DependencyInjection;

use Drjele\DoctrineEncrypt\Contract\EncryptorInterface;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\YamlFileLoader;

class DrjeleDoctrineEncryptExtension extends Extension
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

        $container->setParameter('drjele_doctrine_encrypt.salt', $config['salt']);

        $alias = $container->setAlias(EncryptorInterface::class, $config['encryptor_class']);
        /* this is done to be able to get it from the container */
        $alias->setPublic(true)
            ->setPrivate(false);
    }
}
