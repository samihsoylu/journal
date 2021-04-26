<?php declare(strict_types=1);

namespace App\Utility;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Exception\WrongKeyOrModifiedCiphertextException;
use Defuse\Crypto\Key;
use Defuse\Crypto\KeyProtectedByPassword;

class Encryptor
{
    /**
     * @return string protected encryption key that should be stored in the database
     */
    public function generateProtectedKey(string $password): string
    {
        $protectedKeyObject = KeyProtectedByPassword::createRandomPasswordProtectedKey($password);

        return $protectedKeyObject->saveToAsciiSafeString();
    }

    /**
     * @return string encoded key that should be stored in the user session
     */
    public function getEncodedKeyFromProtectedKey(string $protectedKey, string $password): string
    {
        $protectedKeyObject = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);
        $keyObject = $protectedKeyObject->unlockKey($password);

        return $keyObject->saveToAsciiSafeString();
    }

    /**
     * @return Key object that you must provide for $this->encrypt() and $this->decrypt()
     */
    public function getKeyObjectFromEncodedKey(string $encodedKey): Key
    {
        return Key::loadFromAsciiSafeString($encodedKey);
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

    /**
     * @return string protected encryption key that should be stored in the database
     */
    public function changePassword(string $protectedKey, $currentPassword, $newPassword): string
    {
        $protectedKeyObject = KeyProtectedByPassword::loadFromAsciiSafeString($protectedKey);
        $protectedKeyObject->changePassword($currentPassword, $newPassword);

        return $protectedKeyObject->saveToAsciiSafeString();
    }
}
