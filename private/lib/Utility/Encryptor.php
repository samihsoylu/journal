<?php declare(strict_types=1);

namespace App\Utility;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;

class Encryptor
{
    /**
     * @return string protected encryption key that should be store in the database
     */
    public function generateProtectedEncryptionKey(string $password): string
    {
        $protectedEncryptionKeyObject = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);

        return $protectedEncryptionKeyObject->saveToAsciiSafeString();
    }

    /**
     * @return string encoded key that should be stored in the user session
     */
    public function getEncodedEncryptionKeyFromProtectedEncryptionKey(string $protectedEncryptionKey, string $password): string
    {
        $protectedEncryptionKeyObject = KeyProtectedByPassword::loadFromAsciiSafeString($protectedEncryptionKey);
        $encryptionKeyObject = $protectedEncryptionKeyObject->unlockKey($password);

        return $encryptionKeyObject->saveToAsciiSafeString();
    }

    /**
     * @return Key key object that allows encrypting and decrypting
     */
    public function getKeyFromEncodedEncryptionKey(string $encodedEncryptionKey): Key
    {
        return Key::loadFromAsciiSafeString($encodedEncryptionKey);
    }

    public function encrypt(string $unencryptedString, Key $key): string
    {
        return Crypto::encrypt($unencryptedString, $key);
    }

    /**
     * @throws WrongKeyOrModifiedCiphertextException
     */
    public function decrypt(string $encryptedString, Key $key): string
    {
        return Crypto::decrypt($encryptedString, $key);
    }
}
