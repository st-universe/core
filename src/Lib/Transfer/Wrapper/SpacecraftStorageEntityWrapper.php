<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;

class SpacecraftStorageEntityWrapper implements StorageEntityWrapperInterface
{
    private Spacecraft $spacecraft;

    public function __construct(
        private readonly ShipTorpedoManagerInterface $shipTorpedoManager,
        private readonly SpacecraftStorageCommodityLogic $commodityLogic,
        private readonly SpacecraftStorageCrewLogic $crewLogic,
        private readonly SpacecraftStorageTorpedoLogic $torpedoLogic,
        private SpacecraftWrapperInterface $spacecraftWrapper
    ) {
        $this->spacecraft = $spacecraftWrapper->get();
    }

    // GENERAL
    #[Override]
    public function get(): EntityWithStorageInterface
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getUser(): User
    {
        return $this->spacecraft->getUser();
    }

    #[Override]
    public function getName(): string
    {
        return $this->spacecraft->getName();
    }

    #[Override]
    public function canTransfer(InformationInterface $information): bool
    {
        if (!$this->spacecraft->hasEnoughCrew()) {
            $information->addInformation("Ungenügend Crew vorhanden");
            return false;
        }

        return true;
    }

    #[Override]
    public function getLocation(): Location
    {
        return $this->spacecraft->getLocation();
    }

    // COMMODITIES
    #[Override]
    public function getBeamFactor(): int
    {
        return $this->spacecraft->getRump()->getBeamFactor();
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {

        $this->commodityLogic->transfer($isUnload, $this->spacecraftWrapper, $target, $information);
    }

    // CREW
    #[Override]
    public function getMaxTransferrableCrew(bool $isTarget, User $user): int
    {
        return $this->crewLogic->getMaxTransferrableCrew($this->spacecraft, $isTarget, $user);
    }

    #[Override]
    public function getFreeCrewSpace(User $user): int
    {
        return $this->crewLogic->getFreeCrewSpace($this->spacecraft, $user);
    }

    #[Override]
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool
    {
        return $this->crewLogic->checkCrewStorage($this->spacecraftWrapper, $amount, $isUnload, $information);
    }

    #[Override]
    public function acceptsCrewFrom(int $amount, User $user, InformationInterface $information): bool
    {
        return $this->crewLogic->acceptsCrewFrom($this->spacecraftWrapper, $amount, $user, $information);
    }

    #[Override]
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void
    {
        $this->crewLogic->postCrewTransfer($this->spacecraftWrapper, $foreignCrewChangeAmount, $information);
    }

    // TORPEDOS

    #[Override]
    public function getTorpedo(): ?TorpedoType
    {
        return $this->spacecraft->getTorpedo();
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        return $this->spacecraft->getTorpedoCount();
    }

    #[Override]
    public function getMaxTorpedos(): int
    {
        return $this->spacecraft->getMaxTorpedos();
    }

    #[Override]
    public function canTransferTorpedos(InformationInterface $information): bool
    {
        return $this->torpedoLogic->canTransferTorpedos($this->spacecraft, $information);
    }

    #[Override]
    public function canStoreTorpedoType(TorpedoType $torpedoType, InformationInterface $information): bool
    {
        return $this->torpedoLogic->canStoreTorpedoType($this->spacecraft, $torpedoType, $information);
    }

    #[Override]
    public function changeTorpedo(int $changeAmount, TorpedoType $type): void
    {
        $this->shipTorpedoManager->changeTorpedo(
            $this->spacecraftWrapper,
            $changeAmount,
            $type
        );
    }
}
