<?php

namespace App\exception\image;

class ImageNotGcashReceiptException extends  \Exception {

    public function __construct()
    {
        parent::__construct("Image It not a valid gcash receipt");
    }
}