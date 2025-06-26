<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Entity\TorpedoType;
use Stu\Orm\Entity\User;

interface StorageEntityWrapperInterface
{
    // GENERAL
    public function get(): EntityWithStorageInterface;
    public function getUser(): User;
    public function getName(): string;
    public function getLocation(): Location;
    public function canTransfer(InformationInterface $information): bool;

    // COMMODITIES
    public function getBeamFactor(): int;
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void;

    // CREW
    public function getMaxTransferrableCrew(bool $isTarget, User $user): int;
    public function getFreeCrewSpace(User $user): int;
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool;
    public function acceptsCrewFrom(int $amount, User $user, InformationInterface $information): bool;
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void;

    // TORPEDOS
    public function getTorpedo(): ?TorpedoType;
    public function getTorpedoCount(): int;
    public function getMaxTorpedos(): int;
    public function canTransferTorpedos(InformationInterface $information): bool;
    public function canStoreTorpedoType(TorpedoType $torpedoType, InformationInterface $information): bool;
    public function changeTorpedo(int $changeAmount, TorpedoType $type): void;
}
