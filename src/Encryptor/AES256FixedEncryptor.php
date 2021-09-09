<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\Doctrine\Encrypt\Encryptor;

use Drjele\Doctrine\Encrypt\Contract\EncryptorInterface;
use Drjele\Doctrine\Encrypt\Exception\Exception;
use Drjele\Doctrine\Encrypt\Type\AES256FixedType;

class AES256FixedEncryptor extends AbstractEncryptor implements EncryptorInterface
{
    private const ALGORITHM = 'AES-256-CTR';
    private const HASH_ALGORITHM = 'sha256';
    private const MINIMUM_KEY_LENGTH = 32;
    private const GLUE = "\0";

    public function __construct(string $salt)
    {
        if (!\is_string($salt) || \mb_strlen($salt) < static::MINIMUM_KEY_LENGTH) {
            throw new Exception('invalid encryption salt');
        }

        parent::__construct($salt);
    }

    public function getTypeClass(): ?string
    {
        return AES256FixedType::class;
    }

    public function encrypt(string $data): string
    {
        $nonce = $this->generateNonce($data);
        $plaintext = \serialize($data);

        $ciphertext = \openssl_encrypt(
            $plaintext,
            static::ALGORITHM,
            $this->salt,
            \OPENSSL_RAW_DATA,
            $nonce
        );

        $mac = \hash(static::HASH_ALGORITHM, static::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        return \implode(
            static::GLUE,
            [
                static::ENCRYPTION_MARKER,
                \base64_encode($ciphertext),
                \base64_encode($mac),
                \base64_encode($nonce),
            ]
        );
    }

    public function decrypt(string $data): string
    {
        if (0 !== \mb_strpos($data, static::ENCRYPTION_MARKER . static::GLUE, 0)) {
            /* @todo have an option in the bundle config to return or throw exception */
            return $data;
        }

        $parts = \explode(static::GLUE, $data);

        if (4 !== \count($parts)) {
            throw new Exception('could not validate ciphertext');
        }

        [$_, $ciphertext, $mac, $nonce] = $parts;

        if (false === ($ciphertext = \base64_decode($ciphertext, true))) {
            throw new Exception('could not validate ciphertext');
        }

        if (false === ($mac = \base64_decode($mac, true))) {
            throw new Exception('could not validate mac');
        }

        if (false === ($nonce = \base64_decode($nonce, true))) {
            throw new Exception('could not validate nonce');
        }

        $expected = \hash(static::HASH_ALGORITHM, static::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        if (!\hash_equals($expected, $mac)) {
            throw new Exception('invalid mac');
        }

        $plaintext = \openssl_decrypt(
            $ciphertext,
            static::ALGORITHM,
            $this->salt,
            \OPENSSL_RAW_DATA,
            $nonce
        );

        if (false === $plaintext) {
            throw new Exception('could not decrypt ciphertext');
        }

        return \unserialize($plaintext);
    }

    private function generateNonce(string $data): string
    {
        $dataSize = \strlen($data);

        if (0 === $dataSize) {
            $dataSize = 10;

            $data = \str_repeat('0', $dataSize);
        }

        $size = \openssl_cipher_iv_length(static::ALGORITHM);

        $nonce = '';

        for ($i = 1; $i <= $size; ++$i) {
            $nonce .= $data[($i % $dataSize) - 1];
        }

        return $nonce;
    }
}
