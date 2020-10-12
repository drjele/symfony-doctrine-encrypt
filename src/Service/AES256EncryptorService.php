<?php

declare(strict_types=1);

/*
 * Copyright (c) Adrian Jeledintan
 */

namespace Drjele\DoctrineEncrypt\Service;

use Drjele\DoctrineEncrypt\Exception\Exception;

class AES256EncryptorService extends AbstractEncryptorService
{
    const ALGORITHM = 'AES-256-GCM';
    const HASH_ALGORITHM = 'sha256';
    const MINIMUM_KEY_LENGTH = 32;

    public function __construct(string $salt)
    {
        if (!\is_string($salt) || mb_strlen($salt) < self::MINIMUM_KEY_LENGTH) {
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
            self::ALGORITHM,
            $this->salt,
            OPENSSL_RAW_DATA,
            $nonce
        );

        $mac = hash(self::HASH_ALGORITHM, self::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        return "<ENC>\0" . base64_encode($ciphertext) . "\0" . base64_encode($mac) . "\0" . base64_encode($nonce);
    }

    public function decrypt(string $data): string
    {
        if (0 !== mb_strpos($data, "<ENC>\0", 0)) {
            throw new Exception('Could not validate ciphertext');
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

        $expected = hash(self::HASH_ALGORITHM, self::ALGORITHM . $ciphertext . $this->salt . $nonce, true);

        if (!hash_equals($expected, $mac)) {
            throw new Exception('Invalid MAC');
        }

        $plaintext = openssl_decrypt(
            $ciphertext,
            self::ALGORITHM,
            $this->salt,
            OPENSSL_RAW_DATA,
            $nonce
        );

        if (false === $plaintext) {
            throw new Exception('Could not decrypt ciphertext');
        }

        return unserialize($plaintext);
    }

    protected function generateNonce(): string
    {
        $size = openssl_cipher_iv_length(self::ALGORITHM);

        return random_bytes($size);
    }
}
