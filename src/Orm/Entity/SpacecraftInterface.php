<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Map\DirectionEnum;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\SpacecraftTypeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Interaction\EntityWithInteractionCheckInterface;
use Stu\Lib\Map\EntityWithLocationInterface;
use Stu\Lib\Transfer\EntityWithStorageInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;
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

    public function getUserId(): int;

    public function getUser(): UserInterface;

    public function getUserName(): string;

    public function getFleet(): ?FleetInterface;

    public function getType(): SpacecraftTypeEnum;

    public function getSystemsId(): ?int;

    public function getLayer(): ?LayerInterface;

    public function getFlightDirection(): ?DirectionEnum;

    public function setFlightDirection(DirectionEnum $direction): SpacecraftInterface;

    public function getName(): string;

    public function setName(string $name): SpacecraftInterface;

    public function getLssMode(): SpacecraftLssModeEnum;

    public function setLssMode(SpacecraftLssModeEnum $lssMode): SpacecraftInterface;

    public function getAlertState(): SpacecraftAlertStateEnum;

    public function setAlertState(SpacecraftAlertStateEnum $alertState): SpacecraftInterface;

    public function setAlertStateGreen(): SpacecraftInterface;

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

    public function getHull(): int;

    public function setHuell(int $hull): SpacecraftInterface;

    public function getMaxHull(): int;

    public function setMaxHuell(int $maxHull): SpacecraftInterface;

    public function getShield(): int;

    public function setShield(int $schilde): SpacecraftInterface;

    public function getMaxShield(bool $isTheoretical = false): int;

    public function setMaxShield(int $maxShields): SpacecraftInterface;

    public function getHealthPercentage(): float;

    public function isShielded(): bool;

    public function getNbs(): bool;

    public function getLss(): bool;

    public function getPhaserState(): bool;

    public function isAlertGreen(): bool;

    public function getTorpedoState(): bool;

    public function getTorpedoCount(): int;

    public function isStation(): bool;

    public function isShuttle(): bool;

    public function isConstruction(): bool;

    public function getDatabaseId(): ?int;

    public function setDatabaseId(?int $databaseEntryId): SpacecraftInterface;

    public function isDestroyed(): bool;

    public function setIsDestroyed(bool $isDestroyed): SpacecraftInterface;

    public function isDisabled(): bool;

    public function setDisabled(bool $isDisabled): SpacecraftInterface;

    public function getHitChance(): int;

    public function setHitChance(int $hitChance): SpacecraftInterface;

    public function getEvadeChance(): int;

    public function setEvadeChance(int $evadeChance): SpacecraftInterface;

    public function getBaseDamage(): int;

    public function setBaseDamage(int $baseDamage): SpacecraftInterface;

    public function getTractorPayload(): int;

    public function getState(): SpacecraftStateEnum;

    public function setState(SpacecraftStateEnum $state): SpacecraftInterface;

    public function isUnderRepair(): bool;

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

    public function getBeamFactor(): int;

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

    public function getRepairRate(): int;

    public function getRump(): SpacecraftRumpInterface;

    public function setRump(SpacecraftRumpInterface $shipRump): SpacecraftInterface;

    public function getRumpId(): int;

    public function getRumpName(): string;

    public function hasPhaser(): bool;

    public function hasTorpedo(): bool;

    public function hasCloak(): bool;

    public function hasShuttleRamp(): bool;

    public function hasWarpdrive(): bool;

    public function hasReactor(): bool;

    public function hasNbsLss(): bool;

    public function hasUplink(): bool;

    public function getMaxTorpedos(): int;

    public function hasFreeShuttleSpace(?LoggerUtilInterface $loggerUtil): bool;

    /** @return array<int, CommodityInterface> */
    public function getStoredShuttles(): array;

    public function getStoredShuttleCount(): int;

    /**
     * @return CommodityInterface[]
     */
    public function getStoredBuoy(): array;

    public function hasStoredBuoy(): bool;

    public function canMan(): bool;

    public function hasCrewmanOfUser(int $userId): bool;

    public function getHullColorStyle(): string;

    public function isInEmergency(): bool;

    public function setIsInEmergency(bool $inEmergency): SpacecraftInterface;

    public function __toString(): string;
}
