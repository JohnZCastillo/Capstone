<?php
namespace App\model\enum;

class BudgetStatus extends Enum
{
    protected $name = BudgetStatus::class;
    protected $values = array('approved', 'rejected', 'pending', 'failed');

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

}