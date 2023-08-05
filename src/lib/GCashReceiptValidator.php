<?php

namespace App\lib;

use Respect\Validation\Validator as V;

class GCashReceiptValidator{

    static function isValid(array $images): bool{
        
        $GCASH_KEYWORDS = [
            'gcash',
            'amount',
        ];

        foreach ($images['tmp_name'] as $index => $tmp_name){

            $currentImage = $images['tmp_name'][$index];
            $ocrStringResult = Ocr::getText($currentImage);

            $ocrStringResult = strtolower($ocrStringResult);

            foreach ($GCASH_KEYWORDS as $keyword) {
                if (!V::contains($keyword)->validate($ocrStringResult)) {
                    return false;
                }
            }

        }

        return true;
    }

}