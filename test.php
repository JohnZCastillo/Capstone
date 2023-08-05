<?php

function getMonthsOfYear($year) {
    $months = array();

    for ($month = 1; $month <= 12; $month++) {
        $formattedMonth = sprintf('%04d-%02d', $year, $month);
        $dateObject = new DateTime($formattedMonth . '-01');
        $months[] = $dateObject;
    }

    return $months;
}

$currentYear = date('Y');
$monthsForYear = getMonthsOfYear($currentYear);

// Print the generated month-year pairs
foreach ($monthsForYear as $month) {
    var_dump($month);
}