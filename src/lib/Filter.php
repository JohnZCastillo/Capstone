<?php

namespace App\lib;

class Filter {
    static function check($args) {
        return [
        "from" => Helper::existAndNotNull($args,'from') ? Time::nowStartMonth($args['from']) : null,
        "to" => Helper::existAndNotNull($args,'to') ? Time::nowEndMonth($args['to']) : null,
        "status" =>  Helper::existAndNotNull($args,'status')  ? $args['status'] : null,
        ];
    }
}
