<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ColonyShipRepairRepository;

#[Table(name: 'stu_colonies_shiprepair')]
#[Entity(repositoryClass: ColonyShipRepairRepository::class)]
class ColonyShipRepair
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colony_id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[Column(type: 'integer')]
    private int $field_id;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    #[OneToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'ship_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ship $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setFieldId(int $field_id): ColonyShipRepair
    {
        $this->field_id = $field_id;

        return $this;
    }

    public function getFieldId(): int
    {
        return $this->field_id;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ColonyShipRepair
    {
        $this->colony = $colony;
        return $this;
    }

    public function getShip(): Ship
    {
        return $this->ship;
    }

    public function setShip(Ship $ship): ColonyShipRepair
    {
        $this->ship = $ship;
        return $this;
    }
}
