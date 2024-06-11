<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;

class DrjeleDoctrineEncryptExtension extends Extension
{
    public function load(array $configs, ContainerBuilder $containerBuilder): void
    {
        $loader = new PhpFileLoader($containerBuilder, new FileLocator(__DIR__ . '/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $containerBuilder->setParameter('drjele_doctrine_encrypt.salt', $config['salt']);
        $containerBuilder->setParameter('drjele_doctrine_encrypt.enabled_types', $config['enabled_types']);
    }
}
