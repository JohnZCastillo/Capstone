<?php

namespace App\lib;

use Exception;
use thiagoalessio\TesseractOCR\TesseractOCR;

class Ocr {

    /**
     * Return the text form image
     */
    static function getText($image): string {

        if (!Image::isImage([$image])) {
            throw new Exception("Not an Image");
        }

        return (new TesseractOCR($image))->run();
    }

    /**
     * Check if  a text is in image
     * 
     */
    static function inText($image, $text): bool {
        $textInImage = getText($image);
        return str_contains($textInImage, $text);
    }
}
