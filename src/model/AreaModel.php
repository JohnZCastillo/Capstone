<?php

namespace App\model;

use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity]
#[ORM\UniqueConstraint(name: "unique_area", columns: ['block','lot'])]
#[ORM\Table(name: 'area')]
class AreaModel {

    #[ORM\Id]
    #[ORM\Column(type: 'integer')]
    #[ORM\GeneratedValue]
    private $id;

    #[ORM\Column(type: 'integer')]
    private int|null $block;

    #[ORM\Column(type: 'integer')]
    private int|null $lot;

}