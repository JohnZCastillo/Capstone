<?php

namespace App\lib;

use Exception;

class Helper {

    /**
     * Helper function to get value. Basicaly
     * this is just 
     */
    static function getValue($data,$value = null){

        if(!isset($data) || $data == null){
            return $value;
        }

        return $data;
    }

    static function getArrayValue($data,$key,$value = null){

        if(!isset($data[$key]) || $data[$key] == null){
            return $value;
        }

        return $data[$key];
    }

    /**
     * Check weather a variable does exist and not null
     */
    static function existAndNotNull($data,$key = null){
        if($key){
            if(!isset($data[$key])) return false;
            return $data[$key] != null;
        }else{
            if(!isset($data)) return false;
            return $data != null;
        }
    }

}