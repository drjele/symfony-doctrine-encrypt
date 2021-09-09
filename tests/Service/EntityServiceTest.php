<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Test\Service;

use Doctrine\Persistence\ManagerRegistry;
use Doctrine\Persistence\Mapping\ClassMetadata;
use Drjele\Doctrine\Encrypt\Contract\EncryptorInterface;
use Drjele\Doctrine\Encrypt\Encryptor\AES256Encryptor;
use Drjele\Doctrine\Encrypt\Service\EncryptorFactory;
use Drjele\Doctrine\Encrypt\Service\EntityService;
use Drjele\Doctrine\Encrypt\Type\AES256Type;
use Drjele\Symfony\Phpunit\Mock\ManagerRegistryMock;
use Drjele\Symfony\Phpunit\MockDto;
use Drjele\Symfony\Phpunit\TestCase\AbstractTestCase;
use Mockery;
use Mockery\MockInterface;

/**
 * @internal
 */
final class EntityServiceTest extends AbstractTestCase
{
    public static function getMockDto(): MockDto
    {
        return new MockDto(
            EntityService::class,
            [
                ManagerRegistryMock::class,
                new MockDto(EncryptorFactory::class),
            ],
            true
        );
    }

    public function testGetEncryptor(): void
    {
        $class = 'class';
        $field = 'field';
        $salt = \uniqid(\uniqid(\uniqid('', true), true), true);

        /** @var EntityService|MockInterface $mock */
        $mock = $this->get(EntityService::class);

        $encryptorFactoryMock = $this->get(EncryptorFactory::class);
        $encryptorFactoryMock->shouldReceive('getTypeNames')
            ->once()
            ->andReturn([AES256Type::getFullName()]);
        $encryptorFactoryMock->shouldReceive('getEncryptorByType')
            ->once()
            ->andReturn(new AES256Encryptor($salt));

        $classMetadataMock = Mockery::mock(ClassMetadata::class);
        $classMetadataMock->shouldReceive('getFieldNames')
            ->once()
            ->andReturn([$field]);
        $classMetadataMock->shouldReceive('getTypeOfField')
            ->once()
            ->andReturn(AES256Type::getFullName());

        $managerRegistryMock = $this->get(ManagerRegistry::class);
        $managerRegistryMock->shouldReceive('getMetadataFactory')
            ->once()
            ->andReturnSelf();
        $managerRegistryMock->shouldReceive('getMetadataFor')
            ->once()
            ->with($class)
            ->andReturn($classMetadataMock);

        $encryptor = $mock->getEncryptor($class, $field);

        static::assertInstanceOf(EncryptorInterface::class, $encryptor);
    }

    public function testHasEncryptor(): void
    {
        $class = 'class';
        $field = 'field';

        /** @var EntityService|MockInterface $mock */
        $mock = $this->get(EntityService::class);

        $encryptorFactoryMock = $this->get(EncryptorFactory::class);
        $encryptorFactoryMock->shouldReceive('getTypeNames')
            ->once()
            ->andReturn([AES256Type::getFullName()]);

        $classMetadataMock = Mockery::mock(ClassMetadata::class);
        $classMetadataMock->shouldReceive('getFieldNames')
            ->once()
            ->andReturn([$field]);
        $classMetadataMock->shouldReceive('getTypeOfField')
            ->once()
            ->andReturn(AES256Type::getFullName());

        $managerRegistryMock = $this->get(ManagerRegistry::class);
        $managerRegistryMock->shouldReceive('getMetadataFactory')
            ->once()
            ->andReturnSelf();
        $managerRegistryMock->shouldReceive('getMetadataFor')
            ->once()
            ->with($class)
            ->andReturn($classMetadataMock);

        $hasEncryptor = $mock->hasEncryptor($class, $field);

        static::assertTrue($hasEncryptor);
    }

    public function testEncryptDecrypt(): void
    {
        $data = 'data';
        $class = 'class';
        $field = 'field';
        $encryptor = new AES256Encryptor(\uniqid(\uniqid(\uniqid('', true), true), true));

        /** @var EntityService|MockInterface $mock */
        $mock = $this->get(EntityService::class);

        $encryptorFactoryMock = $this->get(EncryptorFactory::class);
        $encryptorFactoryMock->shouldReceive('getTypeNames')
            ->once()
            ->andReturn([AES256Type::getFullName()]);
        $encryptorFactoryMock->shouldReceive('getEncryptorByType')
            ->once()
            ->andReturn($encryptor);

        $classMetadataMock = Mockery::mock(ClassMetadata::class);
        $classMetadataMock->shouldReceive('getFieldNames')
            ->once()
            ->andReturn([$field]);
        $classMetadataMock->shouldReceive('getTypeOfField')
            ->once()
            ->andReturn(AES256Type::getFullName());

        $managerRegistryMock = $this->get(ManagerRegistry::class);
        $managerRegistryMock->shouldReceive('getMetadataFactory')
            ->once()
            ->andReturnSelf();
        $managerRegistryMock->shouldReceive('getMetadataFor')
            ->once()
            ->with($class)
            ->andReturn($classMetadataMock);

        $encryped = $mock->encrypt($data, $class, $field);
        $dencryped = $encryptor->decrypt($encryped);

        static::assertSame($data, $dencryped);
    }

    public function testGetEntitiesWithEncryption(): void
    {
        $field = 'field';

        /** @var EntityService|MockInterface $mock */
        $mock = $this->get(EntityService::class);

        $encryptorFactoryMock = $this->get(EncryptorFactory::class);
        $encryptorFactoryMock->shouldReceive('getTypeNames')
            ->once()
            ->andReturn([AES256Type::getFullName()]);

        $classMetadataMock = Mockery::mock(ClassMetadata::class);
        $classMetadataMock->shouldReceive('getFieldNames')
            ->once()
            ->andReturn([$field]);
        $classMetadataMock->shouldReceive('getTypeOfField')
            ->once()
            ->andReturn(AES256Type::getFullName());
        $classMetadataMock->shouldReceive('getName')
            ->once()
            ->andReturn('test');

        $managerRegistryMock = $this->get(ManagerRegistry::class);
        $managerRegistryMock->shouldReceive('getMetadataFactory')
            ->once()
            ->andReturnSelf();
        $managerRegistryMock->shouldReceive('getAllMetadata')
            ->once()
            ->andReturn([$classMetadataMock]);

        $entites = $mock->getEntitiesWithEncryption();

        static::assertIsArray($entites);
    }
}
