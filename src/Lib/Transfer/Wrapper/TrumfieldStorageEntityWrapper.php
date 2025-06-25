<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Override;
use RuntimeException;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\TrumfieldInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class TrumfieldStorageEntityWrapper implements StorageEntityWrapperInterface
{
    public function __construct(
        private UserRepositoryInterface $userRepository,
        private TrumfieldInterface $trumfield
    ) {}

    // GENERAL
    #[Override]
    public function get(): EntityWithStorageInterface
    {
        return $this->trumfield;
    }

    #[Override]
    public function getUser(): UserInterface
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
    public function getLocation(): LocationInterface
    {
        return $this->trumfield->getLocation();
    }

    // COMMODITIES
    #[Override]
    public function getBeamFactor(): int
    {
        throw new RuntimeException('this should not happen');
    }

    #[Override]
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void {
        throw new RuntimeException('this should not happen');
    }

    // CREW
    #[Override]
    public function getMaxTransferrableCrew(bool $isTarget, UserInterface $user): int
    {
        return 0;
    }

    #[Override]
    public function getFreeCrewSpace(UserInterface $user): int
    {
        return 0;
    }

    #[Override]
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function acceptsCrewFrom(int $amount, UserInterface $user, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void {}

    // TORPEDOS

    #[Override]
    public function getTorpedo(): ?TorpedoTypeInterface
    {
        return null;
    }

    #[Override]
    public function getTorpedoCount(): int
    {
        return 0;
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
    public function canStoreTorpedoType(TorpedoTypeInterface $torpedoType, InformationInterface $information): bool
    {
        return false;
    }

    #[Override]
    public function changeTorpedo(int $changeAmount, TorpedoTypeInterface $type): void
    {
        throw new RuntimeException('this should not happen');
    }
}
