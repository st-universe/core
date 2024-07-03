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
    private int $ship_id = 0;

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

    #[ManyToOne(targetEntity: 'ShipBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipBuildplanInterface $shipBuildplan;

    #[ManyToOne(targetEntity: 'ShipRump')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipRumpInterface $shipRump;

    #[ManyToOne(targetEntity: 'Ship')]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipInterface $ship;

    #[Override]
    public function getId(): int
    {
        return $this->id;
    }

    #[Override]
    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function setShip(ShipInterface $ship): ShipyardShipQueueInterface
    {
        $this->ship = $ship;
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
    public function setRumpId(int $shipRumpId): ShipyardShipQueueInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
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
    public function getRump(): ShipRumpInterface
    {
        return $this->shipRump;
    }

    #[Override]
    public function setRump(ShipRumpInterface $shipRump): ShipyardShipQueueInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    #[Override]
    public function getShipBuildplan(): ShipBuildplanInterface
    {
        return $this->shipBuildplan;
    }

    #[Override]
    public function setShipBuildplan(ShipBuildplanInterface $shipBuildplan): ShipyardShipQueueInterface
    {
        $this->shipBuildplan = $shipBuildplan;

        return $this;
    }
}
