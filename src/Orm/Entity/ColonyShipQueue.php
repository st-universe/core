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
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\Orm\Repository\ColonyShipQueueRepository;

#[Table(name: 'stu_colonies_shipqueue')]
#[Index(name: 'colony_shipqueue_building_function_idx', columns: ['colony_id', 'building_function_id'])]
#[Index(name: 'colony_shipqueue_user_idx', columns: ['user_id'])]
#[Index(name: 'colony_shipqueue_finish_date_idx', columns: ['finish_date'])]
#[Entity(repositoryClass: ColonyShipQueueRepository::class)]
class ColonyShipQueue
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

    #[Column(type: 'smallint', enumType: BuildingFunctionEnum::class)]
    private BuildingFunctionEnum $building_function_id = BuildingFunctionEnum::BASE_CAMP;

    #[Column(type: 'integer', nullable: true)]
    private ?int $mode = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $ship_id = null;

    #[ManyToOne(targetEntity: SpacecraftBuildplan::class)]
    #[JoinColumn(name: 'buildplan_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftBuildplan $spacecraftBuildplan;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private SpacecraftRump $shipRump;

    #[ManyToOne(targetEntity: Colony::class)]
    #[JoinColumn(name: 'colony_id', nullable: false, referencedColumnName: 'id', onDelete: 'CASCADE')]
    private Colony $colony;

    #[OneToOne(targetEntity: Ship::class)]
    #[JoinColumn(name: 'ship_id', referencedColumnName: 'id', onDelete: 'CASCADE')]
    private ?Ship $ship = null;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColony(): Colony
    {
        return $this->colony;
    }

    public function setColony(Colony $colony): ColonyShipQueue
    {
        $this->colony = $colony;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ColonyShipQueue
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

    public function setBuildtime(int $buildtime): ColonyShipQueue
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finish_date;
    }

    public function setFinishDate(int $finishDate): ColonyShipQueue
    {
        $this->finish_date = $finishDate;

        return $this;
    }

    public function getStopDate(): int
    {
        return $this->stop_date;
    }

    public function setStopDate(int $stopDate): ColonyShipQueue
    {
        $this->stop_date = $stopDate;

        return $this;
    }

    public function setBuildingFunction(BuildingFunctionEnum $buildingFunction): ColonyShipQueue
    {
        $this->building_function_id = $buildingFunction;

        return $this;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->shipRump;
    }

    public function setRump(SpacecraftRump $shipRump): ColonyShipQueue
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    public function getSpacecraftBuildplan(): SpacecraftBuildplan
    {
        return $this->spacecraftBuildplan;
    }

    public function setSpacecraftBuildplan(SpacecraftBuildplan $spacecraftBuildplan): ColonyShipQueue
    {
        $this->spacecraftBuildplan = $spacecraftBuildplan;

        return $this;
    }


    public function getMode(): ?int
    {
        return $this->mode;
    }

    public function setMode(?int $mode): ColonyShipQueue
    {
        $this->mode = $mode;
        return $this;
    }

    public function getShip(): ?Ship
    {
        return $this->ship;
    }

    public function setShip(?Ship $ship): ColonyShipQueue
    {
        $this->ship = $ship;
        return $this;
    }
}
