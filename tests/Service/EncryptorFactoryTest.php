<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Test\Service;

use Doctrine\DBAL\Types\Type;
use Drjele\Doctrine\Encrypt\Encryptor\AES256Encryptor;
use Drjele\Doctrine\Encrypt\Encryptor\AES256FixedEncryptor;
use Drjele\Doctrine\Encrypt\Encryptor\FakeEncryptor;
use Drjele\Doctrine\Encrypt\Service\EncryptorFactory;
use Drjele\Doctrine\Encrypt\Type\AES256Type;
use Drjele\Symfony\Phpunit\MockDto;
use Drjele\Symfony\Phpunit\TestCase\AbstractTestCase;
use Mockery\MockInterface;

/**
 * @internal
 */
final class EncryptorFactoryTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        $salt = \uniqid(\uniqid(\uniqid('', true), true), true);

        $encryptors = [
            new AES256Encryptor($salt),
            new AES256FixedEncryptor($salt),
            new FakeEncryptor(),
        ];

        return new MockDto(
            EncryptorFactory::class,
            [$encryptors],
            true
        );
    }

    public function testGetEncryptor(): void
    {
        /** @var EncryptorFactory|MockInterface $mock */
        $mock = $this->get(EncryptorFactory::class);

        $encryptor = $mock->getEncryptor(AES256FixedEncryptor::class);

        static::assertInstanceOf(AES256FixedEncryptor::class, $encryptor);
    }

    public function testGetEncryptorByType(): void
    {
        /** @var EncryptorFactory|MockInterface $mock */
        $mock = $this->get(EncryptorFactory::class);

        $encryptor = $mock->getEncryptorByType(AES256Type::getFullName());

        static::assertInstanceOf(AES256Encryptor::class, $encryptor);
    }

    public function testGetType(): void
    {
        /** @var EncryptorFactory|MockInterface $mock */
        $mock = $this->get(EncryptorFactory::class);

        Type::addType(AES256Type::getFullName(), new AES256Type());

        $type = $mock->getType(AES256Type::getFullName());

        static::assertInstanceOf(AES256Type::class, $type);
    }
}
