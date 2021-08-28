<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\StationShipRepairRepository")
 * @Table(name="stu_station_shiprepair")
 **/
class StationShipRepair implements StationShipRepairInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $station_id;

    /** @Column(type="integer") * */
    private $ship_id;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="station_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $station;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

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

    public function getStation(): ShipInterface
    {
        return $this->station;
    }

    public function setStation(ShipInterface $station): StationShipRepairInterface
    {
        $this->station = $station;
        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): StationShipRepairInterface
    {
        $this->ship = $ship;
        return $this;
    }
}
