<?php
namespace App\model\enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class IssuesStatus extends Enum
{
    protected $name = IssuesStatus::class;
    protected $values = array('resolved', 'rejected','pending');

    public const RESOLVED = 'resolved';
    public const REJECTED = 'rejected';
    public const PENDING = 'pending';

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