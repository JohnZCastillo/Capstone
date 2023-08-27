<?php

namespace App\lib;

class ReferenceExtractor
{

    public  static function extractReference(array $imagePath): array{

        $references = [];

        foreach ($imagePath['tmp_name'] as $index => $tmp_name){

            $currentImage = $imagePath['tmp_name'][$index];
            $ocrStringResult = Ocr::getText($currentImage);
            $ocrStringResult = strtolower($ocrStringResult);

            $pattern = '/ref no\. (\d{4}\s\d{3}\s\d{6})/i';
            preg_match($pattern, $ocrStringResult, $matches);

            if (isset($matches[1])) {
                $references[] = str_replace(' ', '', $matches[1]);
            } else {
                $references[] = null;
            }

        }

        return $references;
    }


}