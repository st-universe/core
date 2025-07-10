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
use Stu\Component\Map\DirectionEnum;
use Stu\Orm\Attribute\TruncateOnGameReset;
use Stu\Orm\Repository\FlightSignatureRepository;

#[Table(name: 'stu_flight_sig')]
#[Index(name: 'flight_sig_user_idx', columns: ['user_id'])]
#[Index(name: 'flight_sig_sensor_result_idx', columns: ['from_direction', 'to_direction', 'time'])]
#[Entity(repositoryClass: FlightSignatureRepository::class)]
#[TruncateOnGameReset]
class FlightSignature
{
    #[Id]
    #[Column(type: 'integer')]
    #[GeneratedValue(strategy: 'IDENTITY')]
    private int $id;

    #[Column(type: 'integer')]
    private int $user_id = 0;

    #[Column(type: 'integer')]
    private int $ship_id = 0;

    #[Column(type: 'integer')]
    private int $rump_id = 0;

    #[Column(type: 'integer')]
    private int $time = 0;

    #[Column(type: 'integer')]
    private int $location_id = 0;

    #[Column(type: 'smallint', length: 1, enumType: DirectionEnum::class, nullable: true)]
    private ?DirectionEnum $from_direction = null;

    #[Column(type: 'smallint', length: 1, enumType: DirectionEnum::class, nullable: true)]
    private ?DirectionEnum $to_direction = null;

    #[Column(type: 'string')]
    private string $ship_name;

    #[Column(type: 'boolean')]
    private bool $is_cloaked = false;

    #[ManyToOne(targetEntity: SpacecraftRump::class)]
    #[JoinColumn(name: 'rump_id', nullable: false, referencedColumnName: 'id')]
    private SpacecraftRump $rump;

    #[ManyToOne(targetEntity: Location::class)]
    #[JoinColumn(name: 'location_id', nullable: false, referencedColumnName: 'id')]
    private Location $location;

    public function getId(): int
    {
        return $this->id;
    }

    public function setUserId(int $userId): FlightSignature
    {
        $this->user_id = $userId;
        return $this;
    }

    public function getShipId(): int
    {
        return $this->ship_id;
    }

    public function setShipId(int $shipId): FlightSignature
    {
        $this->ship_id = $shipId;
        return $this;
    }

    public function getShipName(): string
    {
        return $this->ship_name;
    }

    public function setSpacecraftName(string $name): FlightSignature
    {
        $this->ship_name = $name;
        return $this;
    }

    public function isCloaked(): bool
    {
        return $this->is_cloaked;
    }

    public function setIsCloaked(bool $isCloaked): FlightSignature
    {
        $this->is_cloaked = $isCloaked;
        return $this;
    }

    public function getRump(): SpacecraftRump
    {
        return $this->rump;
    }

    public function setRump(SpacecraftRump $rump): FlightSignature
    {
        $this->rump = $rump;
        return $this;
    }

    public function getTime(): int
    {
        return $this->time;
    }
    public function setTime(int $time): FlightSignature
    {
        $this->time = $time;
        return $this;
    }

    public function getLocation(): Location
    {
        return $this->location;
    }

    public function setLocation(Location $location): FlightSignature
    {
        $this->location = $location;

        return $this;
    }

    public function getFromDirection(): ?DirectionEnum
    {
        return $this->from_direction;
    }

    public function setFromDirection(DirectionEnum $direction): FlightSignature
    {
        $this->from_direction = $direction;
        return $this;
    }

    public function getToDirection(): ?DirectionEnum
    {
        return $this->to_direction;
    }

    public function setToDirection(DirectionEnum $direction): FlightSignature
    {
        $this->to_direction = $direction;
        return $this;
    }
}
