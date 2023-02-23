<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\GeneratedValue;
use Doctrine\ORM\Mapping\Id;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\Table;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\RepairTaskRepository")
 * @Table(
 *     name="stu_repair_task"
 * )
 */
class RepairTask implements RepairTaskInterface
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
     */
    private int $user_id = 0;

    /**
     * @Column(type="integer")
     */
    private int $ship_id = 0;

    /**
     * @Column(type="integer")
     */
    private int $finish_time = 0;

    /**
     * @Column(type="integer")
     */
    private int $system_type = 0;

    /**
     * @Column(type="integer")
     */
    private int $healing_percentage = 0;

    /**
     *
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?UserInterface $user = null;

    /**
     *
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private ?ShipInterface $ship = null;

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

    public function getSystemType(): int
    {
        return $this->system_type;
    }

    public function setSystemType(int $systemType): RepairTaskInterface
    {
        $this->system_type = $systemType;
        return $this;
    }

    public function getHealingPercentage(): int
    {
        return $this->healing_percentage;
    }

    public function setHealingPercentage(int $healingPercentage): RepairTaskInterface
    {
        $this->healing_percentage = $healingPercentage;
        return $this;
    }
}
