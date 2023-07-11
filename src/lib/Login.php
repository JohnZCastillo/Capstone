<?php

namespace App\lib;

use App\model\UserModel;
use Exception;

session_start();

class Login {

    static function login(UserModel $user){
        $_SESSION['user'] = $user;
    }

    static function logout() {
        session_destroy();
    }
 
    static function isLogin() {
        return isset($_SESSION['user']);
    }

    static function getLogin(): UserModel{
        return  $_SESSION['user'];
    }
}