<?php

namespace App\lib;

use thiagoalessio\TesseractOCR\TesseractOCR;
use thiagoalessio\TesseractOCR\TesseractOcrException;

class Ocr {

    /**
     * Comeback to this.
     * Handle Exception so that admin can know that OCR encountered A Problem
     */


    /**
     * Return the text form image
     */
    static function getText($images): string
    {

        $imageData = file_get_contents($images);

        $tmpFileName = tempnam('./ocr_', "ocr");

        file_put_contents($tmpFileName, $imageData);

        try {
            $output = (new TesseractOCR($tmpFileName))->run();
        } catch (TesseractOcrException $e) {
            $output = $e->getMessage();
        } finally {
            unlink($tmpFileName);
        }

        return $output;
    }

}