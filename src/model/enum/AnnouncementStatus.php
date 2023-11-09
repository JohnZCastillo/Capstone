<?php
namespace App\model\enum;

use Doctrine\DBAL\Platforms\AbstractPlatform;

class AnnouncementStatus extends Enum
{
    
    protected $name = AnnouncementStatus::class;
    protected $values = array('posted', 'archived');

    static function posted(){
        return "posted";
    }

    static function archived(){
        return "archived";
    }


}