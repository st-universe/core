<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Shiprump;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ColonyShipQueueRepository")
 * @Table(
 *     name="stu_colonies_shipqueue",
 *     indexes={
 *         @Index(name="colony_building_function_idx", columns={"colony_id","building_function_id"}),
 *         @Index(name="user_idx", columns={"user_id"}),
 *         @Index(name="finish_date_idx", columns={"finish_date"})
 *     }
 * )
 **/
class ColonyShipQueue implements ColonyShipQueueInterface
{
    /** @Id @Column(type="integer") @GeneratedValue * */
    private $id;

    /** @Column(type="integer") * */
    private $colony_id = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer") * */
    private $rump_id = 0;

    /** @Column(type="integer") * */
    private $buildplan_id = 0;

    /** @Column(type="integer") * */
    private $buildtime = 0;

    /** @Column(type="integer") * */
    private $finish_date = 0;

    /** @Column(type="integer") * */
    private $stop_date = 0;

    /** @Column(type="smallint") * */
    private $building_function_id = 0;

    /**
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="buildplan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shipBuildplan;

    public function getId(): int
    {
        return $this->id;
    }

    public function getColonyId(): int
    {
        return $this->colony_id;
    }

    public function setColonyId(int $colonyId): ColonyShipQueueInterface
    {
        $this->colony_id = $colonyId;

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

    public function getRump(): Shiprump
    {
        return new Shiprump($this->getRumpId());
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
