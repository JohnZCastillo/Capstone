<?php

namespace App\lib;

use App\model\MonthlyPaymentModel;
use App\model\TransactionModel;
use Carbon\Carbon;
use Carbon\CarbonInterface;
use Carbon\CarbonPeriod;
use DateTime;

class MonthlyPaymentHelper
{

    public static function generate(DateTime $from, DateTime $to): void
    {

         $coverage = CarbonPeriod::create($from,'1 month',$to);

        foreach ($coverage as $date){
            echo ($date->format('Y-m'));
            echo  '<br>';
            $monthlyPayment = new MonthlyPaymentModel();
            $monthlyPayment->setDate($date);
            $monthlyPayments[] = $date->format('Y-m');
        }

    }

}
