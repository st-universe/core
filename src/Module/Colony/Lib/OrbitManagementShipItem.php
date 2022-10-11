<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class OrbitManagementShipItem
{
    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private ShipInterface $ship;

    private int $userId;

    public function __construct(
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        ShipInterface $ship,
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

    public function getShip(): ShipInterface
    {
        return $this->ship;
    }

    public function getName(): string
    {
        return $this->ship->getName();
    }

    public function canMan(): bool
    {
        return $this->ship->getCrewCount() == 0
            && $this->ship->getBuildplan() !== null
            && $this->ship->getBuildplan()->getCrew() > 0
            && $this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT);
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
        return $this->ship->getIsDestroyed();
    }

    public function ownedByUser(): bool
    {
        return $this->ship->getUser()->getId() === $this->userId;
    }

    public function canLoadTorpedos(): bool
    {
        return $this->ship->getMaxTorpedos() > 0;
    }

    public function getCloakState(): bool
    {
        return $this->ship->getCloakState();
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
        if ($this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        }

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

    public function getReactorLoad(): int
    {

        return $this->ship->getReactorLoad();
    }

    public function getReactorCapacity(): int
    {
        return $this->ship->getReactorCapacity();
    }

    public function getRumpId(): int
    {
        return $this->ship->getRump()->getId();
    }

    public function getFormerRumpId(): int
    {
        return $this->ship->getFormerRumpId();
    }

    public function isTrumfield(): bool
    {
        return $this->ship->isTrumfield();
    }

    public function getRumpName(): string
    {
        return $this->ship->getRump()->getName();
    }

    public function hasShuttleRamp(): bool
    {
        return $this->ship->hasShuttleRamp();
    }

    public function hasWarpcore(): bool
    {
        return $this->ship->hasWarpcore();
    }

    public function hasFusionReactor(): bool
    {
        return $this->ship->hasFusionReactor();
    }

    public function isShuttleRampHealthy(): bool
    {
        return $this->ship->isShuttleRampHealthy();
    }
}
