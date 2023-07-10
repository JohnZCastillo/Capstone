<?php

namespace App\lib;

class Currency{

    static function format($amount){
        return '₱' . number_format($amount, 2, '.', ',');
    }

    static function formatArray($items,$key){

        foreach($items as &$item){
            $item[$key] = self::format($item[$key]);
        }

        return $items;
    }

}