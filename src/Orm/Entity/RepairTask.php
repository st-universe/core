<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\RepairTaskRepository")
 * @Table(
 *     name="stu_repair_task"
 * )
 **/
class RepairTask implements RepairTaskInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer") */
    private $user_id = 0;

    /** @Column(type="integer") */
    private $ship_id = 0;

    /** @Column(type="integer") */
    private $finish_time = 0;

    /** @Column(type="integer") */
    private $system_type = 0;

    /** @Column(type="integer") */
    private $healing_percentage = 0;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): RepairTaskInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): RepairTaskInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function setFinishTime(int $finishTime): RepairTaskInterface
    {
        $this->finish_time = $finishTime;
        return $this;
    }
}
