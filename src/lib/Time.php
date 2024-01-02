<?php

namespace App\lib;

use App\exception\date\InvalidDateFormat;
use DateInterval;
use DateTime;
use PHPUnit\Exception;
use Respect\Validation\Validator as v;

class Time
{

    /**
     * Converts a date string to a DateTime object set on the first day of the month.
     * @param string $date - (Y-m) eg: 2023-12
     * @return DateTime - On Y-m-d format
     * @throws InvalidDateFormat - on invalid format
     */
    static function startMonth(string $date): DateTime
    {
        $format = 'Y-m';

        if (!v::date($format)->validate($date)) {
            throw new InvalidDateFormat("Invalid Date must be in $format");
        }

        return DateTime::createFromFormat('Y-m-d', $date . '-01');
    }

    /**
     * Convert DateTime object to its string representation
     * @param DateTime $date
     * @param string $format
     * @return string
     */
    static function convertToString(DateTime $date, string $format = 'Y-m-d'): string
    {
        return $date->format($format);
    }

    /**
     * Returns the day difference
     * @param string $start
     * @param string $end
     * @return int
     */
    static function dayPast(string $start, string $end): int
    {
        $difference = strtotime($start) - strtotime($end);

        return (($difference / 60) / 60) / 24;
    }


    /**
     * Convert string date '2023-12-01' to '2023-01'.
     *
     * @param string $date - Date in 'Y-m-d' format
     * @return string - Date in 'Y-m' format
     */
    static function toMonth(string $date): string
    {
        return (DateTime::createFromFormat('Y-m-d', $date))->format('Y-m');
    }


    /**
     * Create a timestamp at now time
     */
    static function timestamp()
    {
        return DateTime::createFromFormat('U', time());
    }

    /**
     * Get the first day of the current month in 'Y-m-d' format.
     *
     * @return string - Date formatted as 'Y-m-01'
     */
    static function thisMonth():string
    {
        return date("Y-m-01");
    }

    /**
     * Get the first day of the next month from the current date.
     *
     * @return string - Date formatted as 'Y-m-01'
     */
    static function nextMonth(): string
    {
        return date("Y-m-01", strtotime("+1 month", strtotime(self::thisMonth())));
    }

    /**
     * Return an array of months from start and to end month.
     * The month must start on the first day 2023-12-01.
     * @param string $startMonth
     * @param string $endMonth
     * @return array
     */
    static function getMonths(string $startMonth, string $endMonth): array
    {

        // Create DateTime objects for the start and end months
        $startDateTime = DateTime::createFromFormat('Y-m-01', $startMonth);
        $endDateTime = DateTime::createFromFormat('Y-m-01', $endMonth);

        // Initialize an empty array to store the months
        $months = [];

        // Loop through the months and add them to the array
        while ($startDateTime <= $endDateTime) {
            $months[] = $startDateTime->format('Y-m-01');
            $startDateTime->modify('+1 month'); // Add 1 month
        }

        return $months;
    }

    /**
     * Check if range are valid.
     * Note: from and to are expected to be a valid date eg: 2015-09-31
     * @param string $from
     * @param string $to
     * @return bool
     * @throws /Exception - when incorrect format is suplemented
     */
    static function isValidDateRange(string $from, string $to): bool
    {
        // Create DateTime objects from the input strings
        $fromDate = new DateTime($from);
        $toDate = new DateTime($to);
        return $fromDate <= $toDate;
    }

    /**
     * This function takes a string representing a month in the 'Y-m' format (e.g., '2023-08')
     *  and returns the first day of that month in 'Y-m-d' format.
     * @param string $month
     * @return string
     * @throws InvalidDateFormat
     */
    static function setToFirstDayOfMonth(string $month): string
    {
        if (!self::isValidFormat('Y-m', $month)) {
            throw new InvalidDateFormat();
        }

        $targetMonth = DateTime::createFromFormat('Y-m', $month);
        $targetMonth->setDate($targetMonth->format('Y'), $targetMonth->format('m'), 1);
        return $targetMonth->format('Y-m-d');
    }

    /**
     * This function takes a string representing a month in the 'Y-m' format (e.g., '2023-08')
     *  and returns the first day of that month in 'Y-m-d' format.
     * @param string $month
     * @return string
     * @throws Exception - when invalid format is passed
     */
    static function setToLastDayOfMonth(string $month): string
    {
        $targetMonth = DateTime::createFromFormat('Y-m', $month);
        return $targetMonth->format('Y-m-t');
    }

    /**
     * Convert String Date eg '2023-12-15' to DateTime
     * @param string $stringDate
     * @return DateTime
     */
    static function convertDateStringToDateTime(string $stringDate): DateTime
    {
        return DateTime::createFromFormat('Y-m-d', $stringDate);
    }


    static function convertStringDateMonthToStringDateTime(string $month): string
    {
        $date = DateTime::createFromFormat('Y-m', $month);
        $date->modify('first day of this month');
        return $date->format('Y-m-d');
    }

    /**
     * Convert Date '2023-12-15' to String date
     * @param string $stringDate
     * @return string
     */
    static function convertDateTimeToDateString(DateTime $date): string
    {
        return $date->format('Y-m-d');
    }

    static function getYearFromStringDate(string $stringDate): string
    {
        $date = DateTime::createFromFormat('Y-m-d', $stringDate);
        return $date->format('Y');
    }

    static function getDatesForMonthsOfYear($year)
    {

        $months = array();

        for ($month = 1; $month <= 12; $month++) {
            $formattedMonth = sprintf('%04d-%02d', $year, $month);
            $dateObject = new DateTime($formattedMonth . '-01');
            $months[] = $dateObject;
        }

        return $months;
    }

    static function convertDateStringToDateTimeEndDay(string $stringDate): string
    {
        $dateTime = new DateTime($stringDate);

        $dateTime->setTime(23, 59, 59);

        return $dateTime->format("Y-m-d H:i:s");
    }

    static function convertDateStringToDateTimeStartDay(string $stringDate): string
    {
        $dateTime = new DateTime($stringDate);

        return $dateTime->format("Y-m-d H:i:s");
    }

    static function getYearSpan(int $from, int $add = 2, int $to = null): array
    {

        if (!isset($to)) {
            $to = $currentYear = date("Y");
        }

        $span = [];

        for ($i = $from; $i <= $to + $add; $i++) {
            $span[] = $i;
        }

        return $span;

    }

    static function createFutureTime(int $minutes): DateTime
    {
        $dateTime = new DateTime();
        $newDateTime = clone $dateTime;
        $newDateTime->add(new DateInterval("PT{$minutes}M"));
        return $newDateTime;
    }

    static function isValidFormat(string $format, string $date): bool
    {
        return v::date($format)->validate($date);
    }

    /**
     * Gets the year from the provided DateTime object or the current year if null.
     * @param DateTime|null $date
     * @return int
     */
    static function getCurrentYear(DateTime $date = null): int
    {

        if(!isset($date)){
            $date = new DateTime();
        }

        return (int) $date->format('Y');
    }

}