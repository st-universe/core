<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;

interface SpacecraftInterface extends
    SpacecraftDestroyerInterface,
    EntityWithStorageInterface,
    EntityWithLocationInterface,
    EntityWithCrewAssignmentsInterface,
    EntityWithInteractionCheckInterface
{
    public function getId(): int;

    public function getCondition(): SpacecraftConditionInterface;

    public function setCondition(SpacecraftConditionInterface $condition): SpacecraftInterface;

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function getUserName(): string;

    public function getFleet(): ?FleetInterface;

    public function getType(): SpacecraftTypeEnum;

    public function getLayer(): ?LayerInterface;

    public function getName(): string;

    public function setName(string $name): SpacecraftInterface;

    public function isSystemHealthy(SpacecraftSystemTypeEnum $type): bool;

    public function getSystemState(SpacecraftSystemTypeEnum $type): bool;

    public function getImpulseState(): bool;

    public function getWarpDriveState(): bool;

    public function isWarped(): bool;

    public function isHeldByTholianWeb(): bool;

    public function isCloaked(): bool;

    public function getTachyonState(): bool;

    public function getSubspaceState(): bool;

    public function getRPGModuleState(): bool;

    public function getMaxHull(): int;

    public function setMaxHuell(int $maxHull): SpacecraftInterface;

    public function getMaxShield(bool $isTheoretical = false): int;

    public function setMaxShield(int $maxShields): SpacecraftInterface;

    public function getHealthPercentage(): float;

    public function isShielded(): bool;

    public function getNbs(): bool;

    public function getLss(): bool;

    public function getPhaserState(): bool;

    public function getTorpedoState(): bool;

    public function getTorpedoCount(): int;

    public function isStation(): bool;

    public function isShuttle(): bool;

    public function isConstruction(): bool;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseEntryId): SpacecraftInterface;

    public function getPosX(): int;

    public function getPosY(): int;

    public function getCrewCount(): int;

    public function getNeededCrewCount(): int;

    public function getExcessCrewCount(): int;

    public function hasEnoughCrew(?GameControllerInterface $game = null): bool;

    public function setUser(UserInterface $user): SpacecraftInterface;

    public function getSystem(): ?StarSystemInterface;

    /** @return array<int, ModuleInterface>*/
    public function getModules(): array;

    public function isDeflectorHealthy(): bool;

    public function isMatrixScannerHealthy(): bool;

    public function isTorpedoStorageHealthy(): bool;

    public function isShuttleRampHealthy(): bool;

    public function isWebEmitterHealthy(): bool;

    public function isTractoring(): bool;

    public function isOverColony(): ?ColonyInterface;

    public function isOverSystem(): ?StarSystemInterface;

    public function isWarpPossible(): bool;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function getTorpedoStorage(): ?TorpedoStorageInterface;

    public function setTorpedoStorage(?TorpedoStorageInterface $torpedoStorage): SpacecraftInterface;

    /**
     * @return Collection<int, ShipLogInterface> Ordered by id
     */
    public function getLogbook(): Collection;

    public function getTakeoverActive(): ?ShipTakeoverInterface;

    public function setTakeoverActive(?ShipTakeoverInterface $takeover): SpacecraftInterface;

    public function getTakeoverPassive(): ?ShipTakeoverInterface;

    public function setTakeoverPassive(?ShipTakeoverInterface $takeover): SpacecraftInterface;

    public function setLocation(LocationInterface $location): SpacecraftInterface;

    public function getMap(): ?MapInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function getLocation(): MapInterface|StarSystemMapInterface;

    public function getSectorString(): string;

    public function getBuildplan(): ?SpacecraftBuildplanInterface;

    public function setBuildplan(?SpacecraftBuildplanInterface $spacecraftBuildplan): SpacecraftInterface;

    /**
     * @return Collection<int, SpacecraftSystemInterface>
     */
    public function getSystems(): Collection;

    public function hasSpacecraftSystem(SpacecraftSystemTypeEnum $type): bool;

    public function getSpacecraftSystem(SpacecraftSystemTypeEnum $type): SpacecraftSystemInterface;

    public function displayNbsActions(): bool;

    public function isTractorbeamPossible(): bool;

    public function isBoardingPossible(): bool;

    public function isInterceptable(): bool;

    public function getTractoredShip(): ?ShipInterface;

    public function setTractoredShip(?ShipInterface $ship): SpacecraftInterface;

    public function getHoldingWeb(): ?TholianWebInterface;

    public function setHoldingWeb(?TholianWebInterface $web): SpacecraftInterface;

    public function getHoldingWebBackgroundStyle(): string;

    public function canIntercept(): bool;

    public function canMove(): bool;

    public function hasActiveWeapon(): bool;

    public function hasEscapePods(): bool;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $shipRump): SpacecraftInterface;

    public function getRumpId(): int;

    public function getRumpName(): string;

    public function hasComputer(): bool;

    public function hasPhaser(): bool;

    public function hasTorpedo(): bool;

    public function hasCloak(): bool;

    public function hasShuttleRamp(): bool;

    public function hasWarpdrive(): bool;

    public function hasReactor(): bool;

    public function hasNbs(): bool;

    public function hasLss(): bool;

    public function hasUplink(): bool;

    public function getMaxTorpedos(): int;

    /** @return Collection<int, CommodityInterface> */
    public function getStoredShuttles(): Collection;

    public function hasStoredBuoy(): bool;

    public function getHullColorStyle(): string;

    public function getState(): SpacecraftStateEnum;

    public function __toString(): string;
}
