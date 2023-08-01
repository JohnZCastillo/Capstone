<?php

require './vendor/autoload.php';


use thiagoalessio\TesseractOCR\TesseractOCR;

echo (new TesseractOCR('sample_gcash.jpeg'))
    ->run();