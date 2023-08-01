<?php

namespace App\exception\image;

class UnsupportedImageException extends  \Exception {

    public function __construct()
    {
        parent::__construct("Image File Type is Unsupported");
    }
}