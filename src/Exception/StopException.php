<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Exception;

/**
 * used to stop a flow
 * if thrown should also always be caught in this package.
 *
 * @internal
 */
final class StopException extends Exception
{
}
