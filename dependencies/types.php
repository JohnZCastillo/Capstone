<?php

use App\model\enum\AnnouncementStatus;
use App\model\enum\BudgetStatus;
use App\model\enum\IssuesStatus;
use App\model\enum\ProjectStatus;
use App\model\enum\UserRole;
use Doctrine\DBAL\Types\Type;

$customTypes = [
    AnnouncementStatus::class,
    IssuesStatus::class,
    UserRole::class,
    BudgetStatus::class,
    ProjectStatus::class
];

foreach ($customTypes as $customTypeClass) {
    if (!Type::hasType($customTypeClass)) {
        Type::addType($customTypeClass, $customTypeClass);
    }
}