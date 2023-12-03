<?php


namespace App\lib;


class Login
{

    static function login($userId)
    {
        $_SESSION['user'] = $userId;
    }

    static function logout()
    {
        session_destroy();
    }

    static function isLogin()
    {
        return isset($_SESSION['user']);
    }


    static function forceLogout()
    {
        session_regenerate_id();
        session_destroy();
    }

    static function getLogin()
    {
        return $_SESSION['user'];
    }

    static function offlinePassword($pass)
    {
        $_SESSION['offlinePassword'] = $pass;
    }

    static function offlineUsername($username)
    {
        $_SESSION['offlineUsername'] = $username;
    }

    static function isOfflineLogin()
    {
        return isset($_SESSION['offlineLogin'])
            ? $_SESSION['offlineLogin']
            : false;
    }

    static function setOfflineLogin(bool $value)
    {
        $_SESSION['offlineLogin'] = $value;
    }
}