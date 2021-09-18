<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\Component\Crew\CrewEnum;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\ShipCrewRepository")
 * @Table(
 *     name="stu_ships_crew",
 *     indexes={
 *         @Index(name="ship_crew_ship_idx", columns={"ships_id"}),
 *         @Index(name="ship_crew_user_idx", columns={"user_id"})
 *     }
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

    /** @Column(type="integer") * */
    private $ships_id = 0;

    /** @Column(type="integer") * */
    private $crew_id = 0;

    /** @Column(type="smallint") * */
    private $slot = 0;

    /** @Column(type="integer") * */
    private $user_id = 0;

    /**
     * @ManyToOne(targetEntity="Crew")
     * @JoinColumn(name="crew_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $crew;

    /**
     * @ManyToOne(targetEntity="Ship")
     * @JoinColumn(name="ships_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $ship;

    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

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
