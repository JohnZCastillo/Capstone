<?php

namespace App\lib;

class NumberFormat
{

    static function formatArray(array &$data, array $keys)
    {

        foreach ($keys as $key){
            $data[$key] = number_format(  $data[$key], 2, '.', ',');
        }


    }

    static function format(&$data)
    {
        $data = number_format(  $data, 2, '.', ',');

    }
}