<?php
namespace App\model\enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use phpDocumentor\Reflection\Types\This;

class LogsTag extends Enum
{
    protected $name = LogsTag::class;
    protected $values = array('block','bill', 'expense', 'income','fund','announcement','user','issue','payment','due','manual payment','payment settings','payment report','system settings','privileges');
    static $staticValues = array('block','bill', 'expense', 'income','fund','announcement','user','issue','payment','due','manual payment','payment settings','payment report','system settings','privileges');

    public static function bill() {
        return 'bill';
    }

    public static function expense() {
        return 'expense';
    }

    public static function income() {
        return 'income';
    }

    public static function fund() {
        return 'fund';
    }

    public static function announcement() {
        return 'announcement';
    }

    public static function user() {
        return 'user';
    }

    public static function issue() {
        return 'issue';
    }

    public static function payment() {
        return 'payment';
    }

    public static function due() {
        return 'due';
    }

    public static function manualPayment() {
        return 'manual payment';
    }

    public static function paymentSettings() {
        return 'payment settings';
    }

    public static function paymentReport() {
        return 'payment report';
    }

    public static function systemSettings() {
        return 'payment report';
    }

    public static function privileges() {
        return 'privileges';
    }

    public static function userBlock() {
        return 'block';
    }

    public static function getValues()
    {
        sort(self::$staticValues);
        return self::$staticValues;
    }
}