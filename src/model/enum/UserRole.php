<?php
namespace App\model\enum;

class UserRole extends Enum
{
    protected $name = 'role';
    protected $values = array('admin', 'user', 'super');

    static function admin(){
        return 'admin';
    }

    static function user(){
        return 'user';
    }

    static function superAdmin(){
        return 'super';
    }
}