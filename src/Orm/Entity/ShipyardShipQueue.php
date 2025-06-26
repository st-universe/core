<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\Index;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Orm\Repository\ShipyardShipQueueRepository;

#[Table(name: 'stu_shipyard_shipqueue')]
#[Index(name: 'shipyard_shipqueue_user_idx', columns: ['user_id'])]
#[Index(name: 'shipyard_shipqueue_finish_date_idx', columns: ['finish_date'])]
#[Entity(repositoryClass: ShipyardShipQueueRepository::class)]
class ShipyardShipQueue
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $station_id = 0;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $buildplan_id = 0;

    #[Column(type: 'integer')]
    private int $buildtime = 0;

    #[Column(type: 'integer')]
    private int $finish_date = 0;

    #[Column(type: 'integer')]
    private int $stop_date = 0;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplan $spacecraftBuildplan;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $spacecraftRump;

    #[ManyToOne(targetEntity: Station::class)]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Station $station;

    public function getId(): int
    {
        return $this->id;
    }

    public function getStation(): Station
    {
        return $this->station;
    }

    public function setStation(Station $station): ShipyardShipQueue
    {
        $this->station = $station;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ShipyardShipQueue
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ShipyardShipQueue
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finish_date;
    }

    public function setFinishDate(int $finishDate): ShipyardShipQueue
    {
        $this->finish_date = $finishDate;

        return $this;
    }

    public function getStopDate(): int
    {
        return $this->stop_date;
    }

    public function setStopDate(int $stopDate): ShipyardShipQueue
    {
        $this->stop_date = $stopDate;

        return $this;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->spacecraftRump;
    }

    public function setRump(SpacecraftRump $spacecraftRump): ShipyardShipQueue
    {
        $this->spacecraftRump = $spacecraftRump;

        return $this;
    }

    public function getSpacecraftBuildplan(): SpacecraftBuildplan
    {
        return $this->spacecraftBuildplan;
    }

    public function setSpacecraftBuildplan(SpacecraftBuildplan $spacecraftBuildplan): ShipyardShipQueue
    {
        $this->spacecraftBuildplan = $spacecraftBuildplan;

        return $this;
    }
}
