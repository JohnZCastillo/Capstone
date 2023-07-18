<?php
namespace App\model\enum;

class IssuesStatus extends Enum
{
    protected $name = 'role';
    protected $values = array('resolved', 'rejected','pending');

    static function resolve(){
        return 'resolve';
    }

    static function rejected(){
        return 'rejected';
    }

    static function pending(){
        return 'pending';
    }
}