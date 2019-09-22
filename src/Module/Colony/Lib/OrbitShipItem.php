<?php

// @todo active strict typing
declare(strict_types=0);

namespace Stu\Module\Colony\Lib;

use ShipData;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class OrbitShipItem implements OrbitShipItemInterface
{
    private $torpedoTypeRepository;

    private $ship;

    private $userId;

    public function __construct(
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        ShipData $ship,
        int $userId
    ) {
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->ship = $ship;
        $this->userId = $userId;
    }

    public function getId(): int
    {
        return $this->ship->getId();
    }

    public function getName(): string
    {
        return $this->ship->getName();
    }

    public function canMan(): bool
    {
        return $this->ship->getCrewCount() == 0 &&
            $this->ship->getBuildplan()->getCrew() > 0;
    }

    public function getCrewCount(): int
    {
        return $this->ship->getCrewCount();
    }

    public function getCrewSlots(): int
    {
        return $this->ship->getBuildplan()->getCrew();
    }

    public function isDestroyed(): bool
    {
        return $this->ship->isDestroyed();
    }

    public function ownedByUser(): bool
    {
        return $this->ship->getUserId() == $this->userId;
    }

    public function canLoadTorpedos(): bool
    {
        return $this->ship->getMaxTorpedos() > 0;
    }

    public function getTorpedoCount(): int
    {
        return $this->ship->getTorpedoCount();
    }

    public function getTorpedoCapacity(): int
    {
        return $this->ship->getMaxTorpedos();
    }

    public function getTorpedoType(): ?TorpedoTypeInterface
    {
        return $this->ship->getTorpedo();
    }

    public function getPossibleTorpedoTypes(): array
    {
        return $this->torpedoTypeRepository->getByLevel($this->ship->getRump()->getTorpedoLevel());
    }

    public function getEbatt(): int
    {
        return $this->ship->getEBatt();
    }

    public function getEbattMax(): int
    {
        return $this->ship->getMaxEbatt();
    }

    public function getWarpCoreLoad(): int
    {
        return $this->ship->getWarpcoreLoad();
    }

    public function getWarpCoreCapacity(): int
    {
        return $this->ship->getWarpcoreCapacity();
    }

    public function getRumpId(): int
    {
        return $this->ship->getRumpId();
    }
}