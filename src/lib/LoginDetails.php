<?php

namespace App\lib;

use DateTime;

class LoginDetails {

    public static function getLoginDetails() :array{

        // Get the IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Simulated device login and session ID (Replace these with actual logic)
        $userAgent = $_SERVER['HTTP_USER_AGENT'];
        $browserInfo = get_browser($userAgent, true);
        $deviceLogin = $browserInfo['browser']. " ".$browserInfo['platform'];

        $loginTime = new DateTime();
        $sessionId = session_id(); // Current session ID

        // Create an array to hold the user information
        return [
            'ipAddress' => $ipAddress,
            'deviceLogin' => $deviceLogin,
            'loginTime' => $loginTime,
            'sessionId' => $sessionId
        ];
    }
}