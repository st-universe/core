<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Entity\TorpedoTypeInterface;
use Stu\Orm\Entity\UserInterface;

interface StorageEntityWrapperInterface
{
    // GENERAL
    public function get(): EntityWithStorageInterface;
    public function getUser(): UserInterface;
    public function getName(): string;
    public function getLocation(): LocationInterface;
    public function canTransfer(InformationInterface $information): bool;

    // COMMODITIES
    public function getBeamFactor(): int;
    public function transfer(
        bool $isUnload,
        StorageEntityWrapperInterface $target,
        InformationInterface $information
    ): void;

    // CREW
    public function getMaxTransferrableCrew(bool $isTarget, UserInterface $user): int;
    public function getFreeCrewSpace(UserInterface $user): int;
    public function checkCrewStorage(int $amount, bool $isUnload, InformationInterface $information): bool;
    public function acceptsCrewFrom(int $amount, UserInterface $user, InformationInterface $information): bool;
    public function postCrewTransfer(int $foreignCrewChangeAmount, StorageEntityWrapperInterface $other, InformationInterface $information): void;

    // TORPEDOS
    public function getTorpedo(): ?TorpedoTypeInterface;
    public function getTorpedoCount(): int;
    public function getMaxTorpedos(): int;
    public function canTransferTorpedos(InformationInterface $information): bool;
    public function canStoreTorpedoType(TorpedoTypeInterface $torpedoType, InformationInterface $information): bool;
    public function changeTorpedo(int $changeAmount, TorpedoTypeInterface $type): void;
}
