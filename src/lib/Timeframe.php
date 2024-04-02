<?php

namespace App\lib;

use App\service\AreaService;
use Carbon\Carbon;
use Carbon\Exceptions\Exception;
use Slim\Views\TwigExtension;

class Timeframe extends TwigExtension{


    public function getFunctions():array
    {
        return [
            new \Twig\TwigFunction('isNotExpired', [$this, 'isNotExpired']),
        ];
    }

    public function isNotExpired(string $date, int $days): int
    {
        return Carbon::createFromFormat('Y-m-d',$date)->diffInDays(Carbon::now()) < $days;
    }

}