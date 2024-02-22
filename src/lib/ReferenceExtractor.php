<?php

namespace App\lib;

class ReferenceExtractor
{

    /**
     * Extracts GCASH References using OCR from an array of images.
     * If reference is not found then a null value is placed.
     *
     * @param array $imagePath Array containing GCASH Receipts images.
     * @return array References obtained from the receipts.
     */

    public static function extractReference(array $imagePath, string $pattern = "/(\d{4}\s\d{3}\s\d{6})/i"): array
    {

        $references = [];

        foreach ($imagePath['tmp_name'] as $index => $tmp_name) {

            $currentImage = $imagePath['tmp_name'][$index];

            $ocrStringResult = Ocr::getText($currentImage);
            $ocrStringResult = strtolower($ocrStringResult);

            preg_match($pattern, $ocrStringResult, $matches);

            if (isset($matches[1])) {
                $references[] = str_replace(' ', '', $matches[1]);
            } else {
                $references[] = null;
            }

        }

        return $references;
    }

    public static function findReference(array $imagePath, string $pattern): array
    {

        $references = [];

        $patterns = explode(';', $pattern);
        $patterns = array_map('trim', $patterns);

        foreach ($imagePath['tmp_name'] as $index => $tmp_name) {

            $currentImage = $imagePath['tmp_name'][$index];

            $ocrStringResult = Ocr::getText($currentImage);
            $ocrStringResult = strtolower($ocrStringResult);

            $found = false;

            foreach ($patterns as $pattern){

                preg_match($pattern, $ocrStringResult, $matches);

                if (isset($matches[1])) {
                    $references[] = str_replace(' ', '', $matches[1]);
                    $found = true;
                    break;
                }

            }

            if(!$found){
                $references[] = null;
            }

        }

        return $references;
    }

    public static function extractOcr(array $imagePath): array
    {

        $ocr = [];

        foreach ($imagePath['tmp_name'] as $index => $tmp_name) {

            $currentImage = $imagePath['tmp_name'][$index];

            $ocrStringResult = Ocr::getText($currentImage);
            $ocrStringResult = strtolower($ocrStringResult);
            $ocr[] =  $ocrStringResult;
        }

        return $ocr;
    }

    public static function reference(array $ocrResult, string $pattern): array
    {

        $references = [];

        $patterns = explode(';', $pattern);
        $patterns = array_map('trim', $patterns);

        foreach ($ocrResult as  $ocrStringResult) {

            $found = false;

            foreach ($patterns as $pattern){

                preg_match($pattern, $ocrStringResult, $matches);

                if (isset($matches[1])) {
                    $references[] = str_replace(' ', '', $matches[1]);
                    $found = true;
                    break;
                }

            }

            if(!$found){
                $references[] = null;
            }

        }

        return $references;
    }

    public static function extractAmount(array $ocrResult, string $pattern = '/amount\s+([\d.,]+)/'): array
    {

        $references = [];

        $patterns = explode(';', $pattern);
        $patterns = array_map('trim', $patterns);

        foreach ($ocrResult as  $ocrStringResult) {

            $found = false;

            foreach ($patterns as $pattern){

                preg_match(strtolower($pattern), $ocrStringResult, $matches);

                if (isset($matches[1])) {
                    $references[] = str_replace(' ', '', $matches[1]);
                    $found = true;
                    break;
                }

            }

            if(!$found){
                $references[] = null;
            }

        }

        return $references;
    }

}