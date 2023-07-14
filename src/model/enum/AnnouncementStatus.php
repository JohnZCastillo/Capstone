<?php
namespace App\model\enum;

class AnnouncementStatus extends Enum
{
    protected $name = 'AnnouncementStatus';
    protected $values = array('posted', 'archived');


    static function posted(){
        return "posted";
    }

    static function archived(){
        return "archived";
    }



}