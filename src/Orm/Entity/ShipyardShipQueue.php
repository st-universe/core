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
use Override;
use Stu\Orm\Repository\ShipyardShipQueueRepository;

#[Table(name: 'stu_shipyard_shipqueue')]
#[Index(name: 'shipyard_shipqueue_user_idx', columns: ['user_id'])]
#[Index(name: 'shipyard_shipqueue_finish_date_idx', columns: ['finish_date'])]
#[Entity(repositoryClass: ShipyardShipQueueRepository::class)]
class ShipyardShipQueue implements ShipyardShipQueueInterface
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
    private SpacecraftBuildplanInterface $spacecraftBuildplan;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRumpInterface $spacecraftRump;

    #[ManyToOne(targetEntity: Station::class)]
    #[JoinColumn(name: 'station_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private StationInterface $station;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getStation(): StationInterface
    {
        return $this->station;
    }

    #[Override]
    public function setStation(StationInterface $station): ShipyardShipQueueInterface
    {
        $this->station = $station;
        return $this;
    }

    #[Override]
    public function getUserId(): int
    {
        return $this->user_id;
    }

    #[Override]
    public function setUserId(int $userId): ShipyardShipQueueInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    #[Override]
    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    #[Override]
    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    #[Override]
    public function setBuildtime(int $buildtime): ShipyardShipQueueInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    #[Override]
    public function getFinishDate(): int
    {
        return $this->finish_date;
    }

    #[Override]
    public function setFinishDate(int $finishDate): ShipyardShipQueueInterface
    {
        $this->finish_date = $finishDate;

        return $this;
    }

    #[Override]
    public function getStopDate(): int
    {
        return $this->stop_date;
    }

    #[Override]
    public function setStopDate(int $stopDate): ShipyardShipQueueInterface
    {
        $this->stop_date = $stopDate;

        return $this;
    }

    #[Override]
    public function getRump(): SpacecraftRumpInterface
    {
        return $this->spacecraftRump;
    }

    #[Override]
    public function setRump(SpacecraftRumpInterface $spacecraftRump): ShipyardShipQueueInterface
    {
        $this->spacecraftRump = $spacecraftRump;

        return $this;
    }

    #[Override]
    public function getSpacecraftBuildplan(): SpacecraftBuildplanInterface
    {
        return $this->spacecraftBuildplan;
    }

    #[Override]
    public function setSpacecraftBuildplan(SpacecraftBuildplanInterface $spacecraftBuildplan): ShipyardShipQueueInterface
    {
        $this->spacecraftBuildplan = $spacecraftBuildplan;

        return $this;
    }
}
