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
        $dotenv = Dotenv::createImmutable(__DIR__ . '/../../');
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

    public static function encryptDumpFile($filePath, $key = null)
    {

        $info = file_get_contents($filePath);


        if ($key) {
            $content = Crypto::encrypt($info, self::loadCustomKey($key));
        } else {
            self::loadKey();
            $content = Crypto::encrypt($info, self::$encryptionKey);
        }

        file_put_contents($filePath, $content);

    }

    public static function decryptDumpFile($filePath, $key = null)
    {

        $info = file_get_contents($filePath);


        if ($key) {
            $content = Crypto::decrypt($info, self::loadCustomKey($key));
        } else {
            self::loadKey();
            $content = Crypto::decrypt($info, self::$encryptionKey);
        }

        file_put_contents($filePath, $content);

    }

    public static function  loadCustomKey($key):Key{
        return Key::loadFromAsciiSafeString($_ENV["ENCRYPTION_KEY"]);
    }

}