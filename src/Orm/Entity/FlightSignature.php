<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FlightSignatureRepository")
 * @Table(
 *     name="stu_flight_sig",
 *     indexes={
 *         @Index(name="flight_sig_map_idx", columns={"map_id"}),
 *         @Index(name="flight_sig_starsystem_map_idx", columns={"starsystem_map_id"})
 *     }
 * )
 **/
class FlightSignature implements FlightSignatureInterface
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
    private $time = 0;

    /** @Column(type="integer", nullable=true) * */
    private $map_id;

    /** @Column(type="integer", nullable=true) * */
    private $starsystem_map_id;

    /** @Column(type="smallint", length=1) */
    private $from_direction = 0;

    /** @Column(type="smallint", length=1) */
    private $to_direction = 0;

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

    /**
     * @ManyToOne(targetEntity="Map")
     * @JoinColumn(name="map_id", referencedColumnName="id")
     */
    private $map;

    /**
     * @ManyToOne(targetEntity="StarSystemMap")
     * @JoinColumn(name="starsystem_map_id", referencedColumnName="id")
     */
    private $starsystem_map;

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

    public function setUser(UserInterface $user): FlightSignatureInterface
    {
        $this->user = $user;
        return $this;
    }

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function setShip(ShipInterface $ship): FlightSignatureInterface
    {
        $this->ship = $ship;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }
    public function setTime(int $time): FlightSignatureInterface
    {
        $this->time = $time;
        return $this;
    }

    public function getMap(): ?MapInterface
    {
        return $this->map;
    }

    public function setMap(?MapInterface $map): FlightSignatureInterface
    {
        $this->map = $map;
        return $this;
    }

    public function getStarsystemMap(): ?StarSystemMapInterface
    {
        return $this->starsystem_map;
    }

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): FlightSignatureInterface
    {
        $this->starsystem_map = $starsystem_map;
        return $this;
    }
}
