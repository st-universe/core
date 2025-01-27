<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\ORM\Mapping\Column;
use Doctrine\ORM\Mapping\Entity;
use Doctrine\ORM\Mapping\JoinColumn;
use Doctrine\ORM\Mapping\ManyToOne;
use Doctrine\ORM\Mapping\OneToOne;
use Doctrine\ORM\Mapping\Table;
use Override;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\StationUtility;
use Stu\Lib\Transfer\TransferEntityTypeEnum;
use Stu\Orm\Repository\ShipRepository;

#[Table(name: 'stu_ship')]
#[Entity(repositoryClass: ShipRepository::class)]
class Ship extends Spacecraft implements ShipInterface
{
    #[Column(type: 'integer', nullable: true)]
    private ?int $fleet_id = null;

    #[Column(type: 'integer', nullable: true)]
    private ?int $docked_to_id = null;

    #[Column(type: 'boolean')]
    private bool $is_fleet_leader = false;

    #[ManyToOne(targetEntity: 'Fleet', inversedBy: 'ships')]
    #[JoinColumn(name: 'fleet_id', referencedColumnName: 'id')]
    private ?FleetInterface $fleet = null;

    #[ManyToOne(targetEntity: 'Station', inversedBy: 'dockedShips')]
    #[JoinColumn(name: 'docked_to_id', referencedColumnName: 'id')]
    private ?StationInterface $dockedTo = null;

    #[OneToOne(targetEntity: 'Spacecraft', mappedBy: 'tractoredShip')]
    private ?SpacecraftInterface $tractoringSpacecraft = null;

    #[OneToOne(targetEntity: 'MiningQueue', mappedBy: 'ship')]
    private ?MiningQueueInterface $miningqueue = null;

    #[OneToOne(targetEntity: 'ColonyShipQueue', mappedBy: 'ship')]
    private ?ColonyShipQueueInterface $colonyShipQueue = null;

    #[Override]
    public function getType(): SpacecraftTypeEnum
    {
        return SpacecraftTypeEnum::SHIP;
    }

    #[Override]
    public function getFleetId(): ?int
    {
        return $this->fleet_id;
    }

    #[Override]
    public function setFleetId(?int $fleetId): ShipInterface
    {
        $this->fleet_id = $fleetId;
        return $this;
    }

    #[Override]
    public function isUnderRetrofit(): bool
    {
        return $this->getState() === SpacecraftStateEnum::RETROFIT;
    }

    #[Override]
    public function getIsFleetLeader(): bool
    {
        return $this->getFleet() !== null && $this->is_fleet_leader;
    }

    #[Override]
    public function setIsFleetLeader(bool $isFleetLeader): ShipInterface
    {
        $this->is_fleet_leader = $isFleetLeader;
        return $this;
    }

    #[Override]
    public function getFleet(): ?FleetInterface
    {
        return $this->fleet;
    }

    #[Override]
    public function setFleet(?FleetInterface $fleet): ShipInterface
    {
        $this->fleet = $fleet;
        return $this;
    }

    #[Override]
    public function isFleetLeader(): bool
    {
        return $this->getIsFleetLeader();
    }

    #[Override]
    public function isTractored(): bool
    {
        return $this->getTractoringSpacecraft() !== null;
    }

    #[Override]
    public function dockedOnTradePost(): bool
    {
        $dockedTo = $this->getDockedTo();

        return $dockedTo !== null
            && $dockedTo->getTradePost() !== null;
    }

    #[Override]
    public function getTractoringSpacecraft(): ?SpacecraftInterface
    {
        return $this->tractoringSpacecraft;
    }

    #[Override]
    public function setTractoringSpacecraft(?SpacecraftInterface $spacecraft): ShipInterface
    {
        $this->tractoringSpacecraft = $spacecraft;
        return $this;
    }

    #[Override]
    public function getDockedTo(): ?StationInterface
    {
        return $this->dockedTo;
    }

    #[Override]
    public function setDockedTo(?StationInterface $dockedTo): ShipInterface
    {
        $this->dockedTo = $dockedTo;
        return $this;
    }

    #[Override]
    public function canBuildConstruction(): bool
    {
        return StationUtility::canShipBuildConstruction($this);
    }

    #[Override]
    public function isWarped(): bool
    {
        $tractoringShip = $this->getTractoringSpacecraft();

        if ($tractoringShip !== null) {
            return $tractoringShip->getWarpDriveState();
        }

        return parent::getWarpDriveState();
    }

    #[Override]
    public function getAstroState(): bool
    {
        return $this->getSystemState(SpacecraftSystemTypeEnum::ASTRO_LABORATORY);
    }

    #[Override]
    public function isBussardCollectorHealthy(): bool
    {
        return $this->isSystemHealthy(SpacecraftSystemTypeEnum::BUSSARD_COLLECTOR);
    }

    #[Override]
    public function getMiningQueue(): ?MiningQueueInterface
    {
        return $this->miningqueue;
    }

    #[Override]
    public function getColonyShipQueue(): ?ColonyShipQueueInterface
    {
        return $this->colonyShipQueue;
    }

    #[Override]
    public function setColonyShipQueue(?ColonyShipQueueInterface $queue): ShipInterface
    {
        $this->colonyShipQueue = $queue;
        return $this;
    }

    #[Override]
    public function getTransferEntityType(): TransferEntityTypeEnum
    {
        return TransferEntityTypeEnum::SHIP;
    }
}
