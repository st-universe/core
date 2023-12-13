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

#[Table(name: 'stu_colonies_shipqueue')]
#[Index(name: 'colony_shipqueue_building_function_idx', columns: ['colony_id', 'building_function_id'])]
#[Index(name: 'colony_shipqueue_user_idx', columns: ['user_id'])]
#[Index(name: 'colony_shipqueue_finish_date_idx', columns: ['finish_date'])]
#[Entity(repositoryClass: 'Stu\Orm\Repository\ColonyShipQueueRepository')]
class ColonyShipQueue implements ColonyShipQueueInterface
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $colony_id = 0;

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

    #[Column(type: 'smallint')]
    private int $building_function_id = 0;

    #[ManyToOne(targetEntity: 'ShipBuildplan')]
    #[JoinColumn(name: 'buildplan_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipBuildplanInterface $shipBuildplan;

    #[ManyToOne(targetEntity: 'ShipRump')]
    #[JoinColumn(name: 'rump_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ShipRumpInterface $shipRump;

    #[ManyToOne(targetEntity: 'Colony')]
    #[JoinColumn(name: 'colony_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ColonyInterface $colony;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColony(): ColonyInterface
    {
        return $this->colony;
    }

    public function setColony(ColonyInterface $colony): ColonyShipQueueInterface
    {
        $this->colony = $colony;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ColonyShipQueueInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ColonyShipQueueInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ColonyShipQueueInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finish_date;
    }

    public function setFinishDate(int $finishDate): ColonyShipQueueInterface
    {
        $this->finish_date = $finishDate;

        return $this;
    }

    public function getStopDate(): int
    {
        return $this->stop_date;
    }

    public function setStopDate(int $stopDate): ColonyShipQueueInterface
    {
        $this->stop_date = $stopDate;

        return $this;
    }

    public function getBuildingFunctionId(): int
    {
        return $this->building_function_id;
    }

    public function setBuildingFunctionId(int $buildingFunctionId): ColonyShipQueueInterface
    {
        $this->building_function_id = $buildingFunctionId;

        return $this;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->shipRump;
    }

    public function setRump(ShipRumpInterface $shipRump): ColonyShipQueueInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    public function getShipBuildplan(): ShipBuildplanInterface
    {
        return $this->shipBuildplan;
    }

    public function setShipBuildplan(ShipBuildplanInterface $shipBuildplan): ColonyShipQueueInterface
    {
        $this->shipBuildplan = $shipBuildplan;

        return $this;
    }
}
