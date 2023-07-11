<?php

namespace App\lib;

use App\model\UserModel;
use Exception;

class Login {

    static function login($userId){
        $_SESSION['user'] = $userId;
    }

    static function logout() {
        session_destroy();
    }
 
    static function isLogin() {
        return isset($_SESSION['user']);
    }

    static function getLogin(){
        return  $_SESSION['user'];
    }
}