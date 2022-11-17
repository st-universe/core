<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Crew\CrewEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipCrewRepository")
 * @Table(
 *     name="stu_ships_crew",
 *     uniqueConstraints={@UniqueConstraint(name="ship_crew_crew_idx", columns={"crew_id"})}
 * )
 **/
class ShipCrew implements ShipCrewInterface
{
    /** 
     * @Id
     * @Column(type="integer")
     * @GeneratedValue(strategy="IDENTITY")
     */
    private $id;

    /** @Column(type="integer", nullable=true) * */
    private $ship_id = 0;

    /** @Column(type="integer") * */
    private $crew_id = 0;

    //TODO make slot nullable for excess crew?
    /** @Column(type="smallint") * */
    private $slot = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /** @Column(type="integer", nullable=true) * */
    private $repair_task_id;

    /**
     * @ManyToOne(targetEntity="Crew")
     * @JoinColumn(name="crew_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $crew;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ship_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="RepairTask")
     * @JoinColumn(name="repair_task_id", referencedColumnName="id")
     */
    private $repairTask;

    public function getId(): int
    {
        return $this->id;
    }

    public function getCrewId(): int
    {
        return $this->crew_id;
    }

    public function setCrewId(int $crewId): ShipCrewInterface
    {
        $this->crew_id = $crewId;

        return $this;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShipId(int $shipId): ShipCrewInterface
    {
        $this->ship_id = $shipId;

        return $this;
    }

    public function getSlot(): int
    {
        return $this->slot;
    }

    public function setSlot(int $slot): ShipCrewInterface
    {
        $this->slot = $slot;

        return $this;
    }

    public function getPosition(): string
    {
        return CrewEnum::getDescription($this->getSlot());
    }

    public function getUserId(): int
    {
        return $this->user_id;
    }

    public function getUser(): UserInterface
    {
        return $this->user;
    }

    public function setUser(UserInterface $user): ShipCrewInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getRepairTask(): ?RepairTaskInterface
    {
        return $this->repairTask;
    }

    public function setRepairTask(?RepairTaskInterface $repairTask): ShipCrewInterface
    {
        $this->repairTask = $repairTask;
        return $this;
    }

    public function getCrew(): CrewInterface
    {
        return $this->crew;
    }

    public function setCrew(CrewInterface $crew): ShipCrewInterface
    {
        $this->crew = $crew;

        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): ShipCrewInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function isForeigner(): bool
    {
        return $this->getShip()->getUser() !== $this->getCrew()->getUser();
    }
}
