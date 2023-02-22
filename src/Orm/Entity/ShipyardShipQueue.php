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
     *
     * @var int
     */
    private $id;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $ship_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $user_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $rump_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $buildplan_id = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $buildtime = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $finish_date = 0;

    /**
     * @Column(type="integer")
     *
     * @var int
     */
    private $stop_date = 0;

    /**
     * @var ShipBuildplanInterface
     *
     * @ManyToOne(targetEntity="ShipBuildplan")
     * @JoinColumn(name="buildplan_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shipBuildplan;

    /**
     * @var ShipRumpInterface
     *
     * @ManyToOne(targetEntity="ShipRump")
     * @JoinColumn(name="rump_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $shipRump;

    /**
     * @var ShipInterface
     *
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
