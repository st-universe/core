<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\StationUtility;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\ShipRepository;

#[Table(name: 'stu_ship')]
#[Entity(repositoryClass: ShipRepository::class)]
class Ship extends Spacecraft
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $fleet_id = null;

    // used for sorting
    #[Column(type: 'boolean')]
    private bool $isFleetLeader = false;

    #[ManyToOne(targetEntity: Fleet::class, inversedBy: 'ships')]
    #[JoinColumn(name: 'fleet_id', referencedColumnName: 'id', nullable: true)]
    private ?Fleet $fleet = null;

    #[ManyToOne(targetEntity: Station::class, inversedBy: 'dockedShips')]
    #[JoinColumn(name: 'docked_to_id', nullable: true, referencedColumnName: 'id')]
    private ?Station $dockedTo = null;

    #[OneToOne(targetEntity: Spacecraft::class, mappedBy: 'tractoredShip')]
    private ?Spacecraft $tractoringSpacecraft = null;

    #[OneToOne(targetEntity: MiningQueue::class, mappedBy: 'ship')]
    private ?MiningQueue $miningqueue = null;

    #[OneToOne(targetEntity: ColonyShipQueue::class, mappedBy: 'ship')]
    private ?ColonyShipQueue $colonyShipQueue = null;

    #[\Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::SHIP;
    }

    public function getFleetId(): ?int
    {
        return $this->fleet_id;
    }

    public function getIsFleetLeader(): bool
    {
        return $this->getFleet() !== null && $this->isFleetLeader;
    }

    public function setIsFleetLeader(bool $isFleetLeader): Ship
    {
        $this->isFleetLeader = $isFleetLeader;
        return $this;
    }

    #[\Override]
    public function getFleet(): ?Fleet
    {
        return $this->fleet;
    }

    public function setFleet(?Fleet $fleet): Ship
    {
        if ($this->fleet === $fleet) {
            return $this;
        }

        $old = $this->fleet;
        $this->fleet = $fleet;

        if ($old !== null) {
            $old->getShips()->removeElement($this);
        }

        if ($fleet !== null && !$fleet->getShips()->contains($this)) {
            $fleet->getShips()->add($this);
        }

        return $this;
    }

    public function isFleetLeader(): bool
    {
        return $this->getIsFleetLeader();
    }

    public function isTractored(): bool
    {
        return $this->getTractoringSpacecraft() !== null;
    }

    public function dockedOnTradePost(): bool
    {
        $dockedTo = $this->getDockedTo();

        return $dockedTo !== null && $dockedTo->getTradePost() !== null;
    }

    public function getTractoringSpacecraft(): ?Spacecraft
    {
        return $this->tractoringSpacecraft;
    }

    public function setTractoringSpacecraft(?Spacecraft $spacecraft): Ship
    {
        $this->tractoringSpacecraft = $spacecraft;
        return $this;
    }

    public function getDockedTo(): ?Station
    {
        return $this->dockedTo;
    }

    public function setDockedTo(?Station $station): Ship
    {
        if ($this->dockedTo === $station) {
            return $this;
        }

        $this->dockedTo = $station;

        if ($station !== null && !$station->getDockedShips()->contains($this)) {
            $station->getDockedShips()->add($this);
        }

        return $this;
    }

    public function canBuildConstruction(): bool
    {
        return StationUtility::canShipBuildConstruction($this);
    }

    #[\Override]
    public function isWarped(): bool
    {
        $tractoringShip = $this->getTractoringSpacecraft();

        if ($tractoringShip !== null) {
            return $tractoringShip->getWarpDriveState();
        }

        return parent::getWarpDriveState();
    }

    public function getAstroState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::ASTRO_LABORATORY);
    }

    public function isBussardCollectorHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR);
    }

    public function getMiningQueue(): ?MiningQueue
    {
        return $this->miningqueue;
    }

    public function getColonyShipQueue(): ?ColonyShipQueue
    {
        return $this->colonyShipQueue;
    }

    public function setColonyShipQueue(?ColonyShipQueue $queue): Ship
    {
        $this->colonyShipQueue = $queue;
        return $this;
    }

    #[\Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::SHIP;
    }
}
