<?php

namespace App\lib;

use DateTime;

class Time {

    /**
     * Create a date with the starting day set to 1.
     * @param string date
     * @return DateTime
     */
    static function startMonth($date) {
        return DateTime::createFromFormat('Y-m-d', $date . '-01');
    }

    /**
     * Create a date with the starting day set to the last day.
     * @param string date
     * @return DateTime
     */
    static function endMonth($date) {
        $date = self::startMonth($date);
        $date =  $date->format('Y-m-t');
        return DateTime::createFromFormat('Y-m-d', $date);
    }

    /**
     * Create a timestamp at now time
     */
    static function timestamp() {
        return DateTime::createFromFormat('U', time());
    }
}
