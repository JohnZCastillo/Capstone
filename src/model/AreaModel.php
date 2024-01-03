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

    public function getId()
    {
        return $this->id;
    }

    public function setId($id): void
    {
        $this->id = $id;
    }

    public function getBlock(): ?int
    {
        return $this->block;
    }

    public function setBlock(?int $block): void
    {
        $this->block = $block;
    }

    public function getLot(): ?int
    {
        return $this->lot;
    }

    public function setLot(?int $lot): void
    {
        $this->lot = $lot;
    }


}