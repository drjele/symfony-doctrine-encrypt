# Doctrine Encrypt Type

The bundle will automatically register **\Drjele\DoctrineEncrypt\Type\EncryptedType** as a Doctrine type.
It can be used for any string field.

## Purpose
Encrypt and decrypt data using Doctrine.

I am trying to solve a few problems that i found with the current offerings:
* have encrypt and decrypt available if using entities or just selecting fields.
* easy where (_for the moment the parameters have to be encrypted before setting them_).

## Todo
* easy where - pass the unencrypted params and have them automatically encrypt.
* multiple encryptors - have the possibility to encrypt different fields with different algorithms.

## Inspired by
* https://github.com/GiveMeAllYourCats/DoctrineEncryptBundle
* https://github.com/jackprice/doctrine-encrypt
