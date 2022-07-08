<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

/**
 * @Entity(repositoryClass="Stu\Orm\Repository\FlightSignatureRepository")
 * @Table(
 *     name="stu_flight_sig",
 *     indexes={
 *         @Index(name="flight_sig_user_idx", columns={"user_id"}),
 *         @Index(name="flight_sig_map_idx", columns={"map_id"}),
 *         @Index(name="flight_sig_starsystem_map_idx", columns={"starsystem_map_id"}),
 *         @Index(name="flight_sig_sensor_result_idx", columns={"from_direction","to_direction","time"})
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
    private $rump_id = 0;

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

    /** @Column(type="string") */
    private $ship_name;

    /** @Column(type="boolean") */
    private $is_cloaked = false;

    //TODO remove reference, should not be deleted if user is removed
    /**
     * @ManyToOne(targetEntity="User")
     * @JoinColumn(name="user_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $user;

    /**
     * @ManyToOne(targetEntity="ShipRump")
     * @JoinColumn(name="rump_id", referencedColumnName="id")
     */
    private $rump;

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

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShipId(int $shipId): FlightSignatureInterface
    {
        $this->ship_id = $shipId;
        return $this;
    }

    public function getShipName(): string
    {
        return $this->ship_name;
    }

    public function setShipName(string $name): FlightSignatureInterface
    {
        $this->ship_name = $name;
        return $this;
    }

    public function isCloaked(): bool
    {
        return $this->is_cloaked;
    }

    public function setIsCloaked(bool $isCloaked): FlightSignatureInterface
    {
        $this->is_cloaked = $isCloaked;
        return $this;
    }

    public function getRump(): ShipRumpInterface
    {
        return $this->rump;
    }

    public function setRump(ShipRumpInterface $shipRump): FlightSignatureInterface
    {
        $this->rump = $shipRump;
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

    public function getFromDirection(): int
    {
        return $this->from_direction;
    }

    public function setFromDirection(int $direction): FlightSignatureInterface
    {
        $this->from_direction = $direction;
        return $this;
    }

    public function getToDirection(): int
    {
        return $this->to_direction;
    }

    public function setToDirection(int $direction): FlightSignatureInterface
    {
        $this->to_direction = $direction;
        return $this;
    }
}
