<?php

namespace App\lib;

use DateTime;
use Detection\MobileDetect;

class LoginDetails {

    public static function getLoginDetails() :array{

        // Get the IP address
        $ipAddress = $_SERVER['REMOTE_ADDR'];

        // Simulated device login and session ID (Replace these with actual logic)
        $userAgent = $_SERVER['HTTP_USER_AGENT'];

        $detect = new MobileDetect();
        $deviceType = $detect->isMobile() ? 'Mobile' : ($detect->isTablet() ? 'Tablet' : 'Desktop');

        // Parse the user agent string to determine the browser
        $browser = "Unknown"; // Default value
        if (preg_match('/MSIE|Trident|Edge/i', $userAgent)) {
            $browser = "Internet Explorer";
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            $browser = "Mozilla Firefox";
        } elseif (preg_match('/Chrome/i', $userAgent)) {
            $browser = "Google Chrome";
        } elseif (preg_match('/Safari/i', $userAgent)) {
            $browser = "Apple Safari";
        } elseif (preg_match('/Opera|OPR/i', $userAgent)) {
            $browser = "Opera";
        }

        $deviceLogin = $deviceType." ".$browser;

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