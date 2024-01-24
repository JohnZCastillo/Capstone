<?php

namespace App\lib;

use Doctrine\DBAL\Driver\PDO\Exception;
use Respect\Validation\Validator as V;

class GCashReceiptValidator
{

    private static array $GCASH_KEYWORDS = ['amount',];

    static function isValid(array $images): bool
    {

        foreach ($images['tmp_name'] as $index => $tmp_name) {

            $currentImage = $images['tmp_name'][$index];
            $ocrStringResult = Ocr::getText($currentImage);

            $ocrStringResult = strtolower($ocrStringResult);

            return V::containsAny(self::$GCASH_KEYWORDS)->validate($ocrStringResult);

        }

        return false;
    }

}