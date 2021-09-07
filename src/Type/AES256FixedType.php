<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Type;

class AES256FixedType extends AbstractType
{
    protected static function getShortName(): string
    {
        return 'AES256fixed';
    }
}
