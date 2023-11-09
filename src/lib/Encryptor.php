<?php

namespace App\lib;

use Defuse\Crypto\Crypto;
use Defuse\Crypto\Key;
use Dotenv\Dotenv;

class Encryptor
{
    private static Key $encryptionKey;

    private static function loadKey()
    {
        $dotenv = Dotenv::createImmutable( $_SERVER['DOCUMENT_ROOT']);
        $dotenv->load();

        self::$encryptionKey = Key::loadFromAsciiSafeString($_ENV["ENCRYPTION_KEY"]);;
    }

    public static function encrypt(string $info): string
    {
        self::loadKey();
        return Crypto::encrypt($info, self::$encryptionKey);
    }

    public static function decrypt(string $encryptedString): string
    {
        self::loadKey();
        return Crypto::decrypt($encryptedString, self::$encryptionKey);
    }

}