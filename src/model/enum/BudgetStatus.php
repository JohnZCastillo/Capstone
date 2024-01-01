<?php
namespace App\model\enum;

class BudgetStatus extends Enum
{
    protected $name = BudgetStatus::class;
    protected $values = array('approved', 'rejected', 'pending', 'failed', 'bill');

    static function approved(){
        return 'approved';
    }

    static function rejected(){
        return 'rejected';
    }

    static function pending(){
        return 'pending';
    }

    static function failed(){
        return 'failed';
    }

    static function bill(){
        return 'bill';
    }

}