<?php

use Respect\Validation\Validator as V;

require './vendor/autoload.php';

$GCASH_KEYWORDS=["amount",'gcash','transaction'];
$target = "Sent Via Gcash amount : 500 transaction no 1231231";

$target = strtolower($target);

foreach ($GCASH_KEYWORDS as $keyword) {
    if (!V::contains($keyword)->validate($target)) {
        echo  "not Gcash";
    }
}
