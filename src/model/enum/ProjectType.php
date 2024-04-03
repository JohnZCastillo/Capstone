<?php
namespace App\model\enum;

class ProjectType extends Enum
{

    const ACTIVE = 'active';
    const ARCHIVE = 'archive';

    protected $name = ProjectType::class;
    protected $values = array(ProjectType::ACTIVE,ProjectType::ARCHIVE);

}