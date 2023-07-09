<?php

namespace App\lib;

use DateTime;

class Time{

    static function date($date){
        return DateTime::createFromFormat('Y-m-d', $date . '-01');
    }

    static function timestamp(){
        return DateTime::createFromFormat('U', time());
    }
}