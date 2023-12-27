<?php

namespace App\lib;

use App\model\PrivilegesModel;

class Redirector
{

    public static function redirectToHome(PrivilegesModel $privileges): string{

        if($privileges->getAdminPayment()){
            return  "/admin/payments";
        }

        if($privileges->getAdminIssues()){
            return  "/admin/issues";
        }

        if($privileges->getAdminAnnouncement()){
            return  "/admin/announcements";
        }

        if($privileges->getAdminUser()){
            return  "/admin/users";
        }

        return "/home";
    }
}