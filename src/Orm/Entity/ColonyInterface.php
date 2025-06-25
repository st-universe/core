<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Lib\Colony\PlanetFieldHostInterface;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;

interface ColonyInterface extends
    PlanetFieldHostInterface,
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface
{
    public function getChangeable(): ColonyChangeableInterface;

    public function getUserId(): int;

    public function getSx(): int;

    public function getSy(): int;

    public function setName(string $name): ColonyInterface;

    public function getPlanetName(): string;

    public function setPlanetName(string $planet_name): ColonyInterface;

    public function getMask(): ?string;

    public function setMask(?string $mask): ColonyInterface;

    public function getDatabaseId(): ?int;

    public function setDatabaseEntry(?DatabaseEntryInterface $entry): ColonyInterface;

    public function getTwilightZone(int $timestamp): int;

    public function setRotationFactor(int $rotationFactor): ColonyInterface;

    public function getRotationTime(): int;

    public function getColonyTimeHour(int $timestamp): ?string;

    public function getColonyTimeMinute(int $timestamp): ?string;

    public function getDayTimePrefix(int $timestamp): ?int;

    public function getDayTimeName(int $timestamp): ?string;

    public function getSurfaceWidth(): int;

    public function setSurfaceWidth(int $surfaceWidth): ColonyInterface;

    public function setColonyClass(ColonyClassInterface $colonyClass): ColonyInterface;

    public function storagePlaceLeft(): bool;

    public function getStarsystemMap(): StarSystemMapInterface;

    public function setStarsystemMap(StarSystemMapInterface $systemMap): ColonyInterface;

    public function getSystem(): StarSystemInterface;

    public function getBeamFactor(): int;

    public function isDefended(): bool;

    /**
     * @return Collection<int, FleetInterface>
     */
    public function getDefenders(): Collection;

    public function isBlocked(): bool;

    /**
     * @return Collection<int, FleetInterface>
     */
    public function getBlockers(): Collection;

    public function getCrewAssignmentAmount(): int;

    public function getCrewTrainingAmount(): int;

    public function isFree(): bool;

    public function setUser(UserInterface $user): ColonyInterface;

    public function getSectorString(): string;
}
