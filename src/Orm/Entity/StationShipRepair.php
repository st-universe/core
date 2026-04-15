<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\StationShipRepairRepository;

#[Table(name: 'stu_station_shiprepair')]
#[Entity(repositoryClass: StationShipRepairRepository::class)]
class StationShipRepair
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $station_id;

    #[Column(type: 'integer')]
    private int $ship_id;

    #[ManyToOne(targetEntity: Station::class)]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Station $station;

    #[ManyToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'ship_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Ship $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStationId(): int
    {
        return $this->station_id;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function getStation(): Station
    {
        return $this->station;
    }

    public function setStation(Station $station): StationShipRepair
    {
        $this->station = $station;
        return $this;
    }

    public function getShip(): Ship
    {
        return $this->ship;
    }

    public function setShip(Ship $ship): StationShipRepair
    {
        $this->ship = $ship;
        return $this;
    }
}
