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


    /**
     * Destroy session except for the pass key
     * @param $toKeep
     * @return void
     */
    static function forceLogout($toKeep = null)
    {

        foreach ($_SESSION as $key => $value) {
            if ($key !== $toKeep){
                unset($_SESSION[$key]);
            }
        }

        session_regenerate_id();

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