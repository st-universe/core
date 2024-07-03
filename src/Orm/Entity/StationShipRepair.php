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
use Doctrine\ORM\Mapping\Table;

#[Table(name: 'stu_station_shiprepair')]
#[Entity(repositoryClass: 'Stu\Orm\Repository\StationShipRepairRepository')]
class StationShipRepair implements StationShipRepairInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $station_id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'station_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $station;

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getStationId(): int
    {
        return $this->station_id;
    }

    #[Override]
    public function getShipId(): int
    {
        return $this->ship_id;
    }

    #[Override]
    public function getStation(): ShipInterface
    {
        return $this->station;
    }

    #[Override]
    public function setStation(ShipInterface $station): StationShipRepairInterface
    {
        $this->station = $station;
        return $this;
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function setShip(ShipInterface $ship): StationShipRepairInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
