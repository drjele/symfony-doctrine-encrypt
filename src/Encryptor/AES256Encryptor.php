<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Encryptor;

use Drjele\DoctrineEncrypt\Exception\Exception;

class AES256Encryptor extends AbstractEncryptor
{
    private const ALGORITHM = 'AES-256-CTR';
    private const HASH_ALGORITHM = 'sha256';
    private const MINIMUM_KEY_LENGTH = 32;

    public function __construct(string $salt)
    {
        if (!\is_string($salt) || mb_strlen($salt) < static::MINIMUM_KEY_LENGTH) {
            throw new Exception('Invalid encryption salt');
        }

        parent::__construct($salt);
    }

    public function encrypt(string $data): string
    {
        $nonce = $this->generateNonce();
        $plaintext = serialize($data);

        $ciphertext = openssl_encrypt(
            $plaintext,
            static::ALGORITHM,
            $this->salt,
            OPENSSL_RAW_DATA,
            $nonce
        );

        $mac = hash(static::HASH_ALGORITHM, static::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        return "<ENC>\0" . base64_encode($ciphertext) . "\0" . base64_encode($mac) . "\0" . base64_encode($nonce);
    }

    public function decrypt(string $data): string
    {
        if (0 !== mb_strpos($data, "<ENC>\0", 0)) {
            /* @todo have an option in the bundle config to return or throw exception */
            return $data;
        }

        $parts = explode("\0", $data);

        if (4 !== \count($parts)) {
            throw new Exception('Could not validate ciphertext');
        }

        [$_, $ciphertext, $mac, $nonce] = $parts;

        if (false === ($ciphertext = base64_decode($ciphertext))) {
            throw new Exception('Could not validate ciphertext');
        }

        if (false === ($mac = base64_decode($mac))) {
            throw new Exception('Could not validate ciphertext');
        }

        if (false === ($nonce = base64_decode($nonce))) {
            throw new Exception('Could not validate ciphertext');
        }

        $expected = hash(static::HASH_ALGORITHM, static::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        if (!hash_equals($expected, $mac)) {
            throw new Exception('Invalid MAC');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            static::ALGORITHM,
            $this->salt,
            OPENSSL_RAW_DATA,
            $nonce
        );

        if (false === $plaintext) {
            throw new Exception('Could not decrypt ciphertext');
        }

        return unserialize($plaintext);
    }

    private function generateNonce(): string
    {
        $size = openssl_cipher_iv_length(static::ALGORITHM);

        return random_bytes($size);
    }
}
