<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_colonies_shiprepair')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ColonyShipRepairRepository')]
class ColonyShipRepair implements ColonyShipRepairInterface
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

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    #[OneToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->ship_id;
    }

    #[Override]
    public function setFieldId(int $field_id): ColonyShipRepairInterface
    {
        $this->field_id = $field_id;

        return $this;
    }

    #[Override]
    public function getFieldId(): int
    {
        return $this->field_id;
    }

    #[Override]
    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    #[Override]
    public function setColony(ColonyInterface $colony): ColonyShipRepairInterface
    {
        $this->colony = $colony;
        return $this;
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function setShip(ShipInterface $ship): ColonyShipRepairInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
