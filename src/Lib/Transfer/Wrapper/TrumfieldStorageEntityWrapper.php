<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use BadMethodCallException;
use Override;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\Trumfield;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;

class TrumfieldStorageEntityWrapper implements StorageEntityWrapperInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private Trumfield $trumfield
    ) {}

    // GENERAL
    #[Override]
    public function get(): EntityWithStorageInterface
    {
        return $this->trumfield;
    }

    #[Override]
    public function getUser(): User
    {
        return $this->userRepository->getFallbackUser();
    }

    #[Override]
    public function getName(): string
    {
        return $this->trumfield->getName();
    }

    #[Override]
    public function canTransfer(InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function getLocation(): Location
    {
        return $this->trumfield->getLocation();
    }

    // COMMODITIES
    #[Override]
    public function getBeamFactor(): int
    {
        throw new BadMethodCallException();
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        throw new BadMethodCallException();
    }

    // CREW
    #[Override]
    public function getMaxTransferrableCrew(bool $isTarget, User $user): int
    {
        return 0;
    }

    #[Override]
    public function getFreeCrewSpace(User $user): int
    {
        return 0;
    }

    #[Override]
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function acceptsCrewFrom(int $amount, User $user, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void
    {
        // nothing to do
    }

    // TORPEDOS

    #[Override]
    public function getTorpedo(): ?TorpedoType
    {
        return null;
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        return $this->getMaxTorpedos();
    }

    #[Override]
    public function getMaxTorpedos(): int
    {
        return 0;
    }

    #[Override]
    public function canTransferTorpedos(InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function canStoreTorpedoType(TorpedoType $torpedoType, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function changeTorpedo(int $changeAmount, TorpedoType $type): void
    {
        throw new BadMethodCallException();
    }
}
