<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipyardShipQueueRepository")
 * @Table(
 *     name="stu_shipyard_shipqueue",
 *     indexes={
 *         @Index(name="shipyard_shipqueue_user_idx", columns={"user_id"}),
 *         @Index(name="shipyard_shipqueue_finish_date_idx", columns={"finish_date"})
 *     }
 * )
 **/
class ShipyardShipQueue implements ShipyardShipQueueInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") * */
    private $ship_id = 0;

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

    /**
     * @ManyToOne(targetEntity="ShipRump")
     * @JoinColumn(name="rump_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shipRump;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ShipyardShipQueueInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function setUserId(int $userId): ShipyardShipQueueInterface
    {
        $this->user_id = $userId;

        return $this;
    }

    public function getRumpId(): int
    {
        return $this->rump_id;
    }

    public function setRumpId(int $shipRumpId): ShipyardShipQueueInterface
    {
        $this->rump_id = $shipRumpId;

        return $this;
    }

    public function getBuildtime(): int
    {
        return $this->buildtime;
    }

    public function setBuildtime(int $buildtime): ShipyardShipQueueInterface
    {
        $this->buildtime = $buildtime;

        return $this;
    }

    public function getFinishDate(): int
    {
        return $this->finish_date;
    }

    public function setFinishDate(int $finishDate): ShipyardShipQueueInterface
    {
        $this->finish_date = $finishDate;

        return $this;
    }

    public function getStopDate(): int
    {
        return $this->stop_date;
    }

    public function setStopDate(int $stopDate): ShipyardShipQueueInterface
    {
        $this->stop_date = $stopDate;

        return $this;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->shipRump;
    }

    public function setRump(ShipRumpInterface $shipRump): ShipyardShipQueueInterface
    {
        $this->shipRump = $shipRump;

        return $this;
    }

    public function getShipBuildplan(): ShipBuildplanInterface
    {
        return $this->shipBuildplan;
    }

    public function setShipBuildplan(ShipBuildplanInterface $shipBuildplan): ShipyardShipQueueInterface
    {
        $this->shipBuildplan = $shipBuildplan;

        return $this;
    }
}
