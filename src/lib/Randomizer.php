<?php

namespace App\lib;

class Randomizer
{

    public static  function generateSixDigit(): int{
        return mt_rand(100000, 999999);
    }

    public static function generateRandomPassword($length = 8): string{
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $password = '';
        $max = strlen($characters) - 1;

        for ($i = 0; $i < $length; $i++) {
            $password .= $characters[random_int(0, $max)];
        }

        return $password;
    }
}