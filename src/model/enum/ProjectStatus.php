<?php
namespace App\model\enum;

class ProjectStatus extends Enum
{

    const COMPLETED = 'completed';
    const ONGOING = 'ongoing';
    const CANCELLED = 'cancelled';

    protected $name = ProjectStatus::class;
    protected $values = array(ProjectStatus::COMPLETED,ProjectStatus::ONGOING,ProjectStatus::CANCELLED);

}