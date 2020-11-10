# Doctrine Encrypt Type

The bundle will automatically register the types from **\Drjele\DoctrineEncrypt\Type** as a Doctrine types.

It can be used for any string field.

**Contributions are welcomed**.

## Purpose
Encrypt and decrypt data using Doctrine.

I am trying to solve a few problems that i found with the current offerings:
* have encrypt and decrypt available if using entities or just selecting fields.
* easy where (_for the moment the parameters have to be encrypted before setting them_).

## Usage
* the value on the entity will always be unencrypted.
* the purpose for **AES256FixedEncryptor**, **AES256FixedType** pair is to be able to use **WHERE**, as it will always return the same result for the same input.
* **EntityService::getEncryptor()** will return the encryptor used for the field, if you need to encrypt a value to use it as a **WHERE** parameter.
* inside entity:
```php
class Customer
{
    /**
     * @ORM\Column(type="encryptedAES256")
     */
    private string $name;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }
}
```
* to encrypt an unencrypted database:
```shell script 
php bin/console drjele:doctrine:database:encrypt
```
* to decrypt an encrypted database:
```shell script 
php bin/console drjele:doctrine:database:decrypt
```

## Todo
* easy where, pass the unencrypted params and have them automatically encrypt.
* configure registered encryptors.

## Inspired by
* https://github.com/GiveMeAllYourCats/DoctrineEncryptBundle
* https://github.com/jackprice/doctrine-encrypt
