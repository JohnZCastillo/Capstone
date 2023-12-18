<?php

use App\model\enum\AnnouncementStatus;
use App\model\enum\BudgetStatus;
use App\model\enum\IssuesStatus;
use App\model\enum\UserRole;
use Doctrine\DBAL\Types\Type;

//Register your enum types here
Type::addType(AnnouncementStatus::class, AnnouncementStatus::class);
Type::addType(IssuesStatus::class, IssuesStatus::class);
Type::addType(UserRole::class, UserRole::class);
Type::addType(BudgetStatus::class, BudgetStatus::class);