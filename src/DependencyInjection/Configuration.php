<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\DependencyInjection;

use Drjele\DoctrineEncrypt\Service\AES256EncryptorService;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('drjele_doctrine_encrypt');

        $treeBuilder->getRootNode()
            ->children()
            ->scalarNode('salt')->isRequired()->end()
            ->scalarNode('encryptor_service_class')->defaultValue(AES256EncryptorService::class)->end();

        return $treeBuilder;
    }
}
