<?php

namespace App\lib;

use DateTime;

class LoginDetails {

    public static function getLoginDetails() :array{

        // Get the IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Simulated device login and session ID (Replace these with actual logic)
        $deviceLogin = "SampleDevice"; // Replace with actual device login retrieval
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