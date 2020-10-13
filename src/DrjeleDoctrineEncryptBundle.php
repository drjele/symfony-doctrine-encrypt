<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt;

use Drjele\DoctrineEncrypt\Service\EncryptorFactory;
use Symfony\Component\HttpKernel\Bundle\Bundle;

class DrjeleDoctrineEncryptBundle extends Bundle
{
    public function boot()
    {
        parent::boot();

        $this->registerTypes();
    }

    private function registerTypes(): void
    {
        /* this required because of how doctrine instantiates its types */

        /** @var EncryptorFactory $factory */
        $factory = $this->container->get(EncryptorFactory::class);

        $factory->registerTypes();
    }
}
