<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\SpacecraftTypeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\Location;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\Destruction\ShipDestroyerInterface;

interface ShipInterface extends ShipDestroyerInterface
{
    public function getId(): int;

    public function getUserId(): int;

    public function getUserName(): string;

    public function getFleetId(): ?int;

    public function setFleetId(?int $fleetId): ShipInterface;

    public function getSystemsId(): ?int;

    public function getLayer(): ?LayerInterface;

    public function getLayerId(): int;

    public function setLayerId(int $layerId): ShipInterface;

    public function getCx(): int;

    public function setCx(int $cx): ShipInterface;

    public function getCy(): int;

    public function setCy(int $cy): ShipInterface;

    public function getSx(): int;

    public function getSy(): int;

    public function getFlightDirection(): int;

    public function setFlightDirection(int $direction): ShipInterface;

    public function getName(): string;

    public function setName(string $name): ShipInterface;

    public function getLSSmode(): int;

    public function setLSSMode(int $lssMode): ShipInterface;

    public function getAlertState(): ShipAlertStateEnum;

    public function setAlertState(ShipAlertStateEnum $alertState): ShipInterface;

    public function setAlertStateGreen(): ShipInterface;

    public function isSystemHealthy(ShipSystemTypeEnum $type): bool;

    public function getSystemState(ShipSystemTypeEnum $type): bool;

    public function getImpulseState(): bool;

    public function getWarpState(bool $mindTractoringShip = false): bool;

    public function getWebState(): bool;

    public function getCloakState(): bool;

    public function getTachyonState(): bool;

    public function getSubspaceState(): bool;

    public function getAstroState(): bool;

    public function getRPGModuleState(): bool;

    public function getConstructionHubState(): bool;

    public function getHull(): int;

    public function setHuell(int $hull): ShipInterface;

    public function getMaxHull(): int;

    public function setMaxHuell(int $maxHull): ShipInterface;

    public function getShield(): int;

    public function setShield(int $schilde): ShipInterface;

    public function getMaxShield(bool $isTheoretical = false): int;

    public function setMaxShield(int $maxShields): ShipInterface;

    public function getHealthPercentage(): float;

    public function getShieldState(): bool;

    public function getNbs(): bool;

    public function getLss(): bool;

    public function getPhaserState(): bool;

    public function isAlertGreen(): bool;

    public function getTorpedoState(): bool;

    public function getFormerRumpId(): int;

    public function setFormerRumpId(int $formerShipRumpId): ShipInterface;

    public function getTorpedoCount(): int;

    public function isBase(): bool;

    public function isTrumfield(): bool;

    public function isShuttle(): bool;

    public function isConstruction(): bool;

    public function getSpacecraftType(): SpacecraftTypeEnum;

    public function setSpacecraftType(SpacecraftTypeEnum $type): ShipInterface;

    public function getDatabaseId(): int;

    public function setDatabaseId(int $databaseEntryId): ShipInterface;

    public function isDestroyed(): bool;

    public function setIsDestroyed(bool $isDestroyed): ShipInterface;

    public function isDisabled(): bool;

    public function setDisabled(bool $isDisabled): ShipInterface;

    public function getHitChance(): int;

    public function setHitChance(int $hitChance): ShipInterface;

    public function getEvadeChance(): int;

    public function setEvadeChance(int $evadeChance): ShipInterface;

    public function getBaseDamage(): int;

    public function setBaseDamage(int $baseDamage): ShipInterface;

    public function getSensorRange(): int;

    public function setSensorRange(int $sensorRange): ShipInterface;

    public function getTractorPayload(): int;

    public function getShieldRegenerationTimer(): int;

    public function setShieldRegenerationTimer(int $shieldRegenerationTimer): ShipInterface;

    public function getState(): ShipStateEnum;

    public function setState(ShipStateEnum $state): ShipInterface;

    public function isUnderRepair(): bool;

    public function getAstroStartTurn(): ?int;

    public function setAstroStartTurn(?int $turn): ShipInterface;

    public function getIsFleetLeader(): bool;

    public function setIsFleetLeader(bool $isFleetLeader): ShipInterface;

    /**
     * @return Collection<int, ShipCrewInterface>
     */
    public function getCrewAssignments(): Collection;

    public function getPosX(): int;

    public function getPosY(): int;

    public function getCrewCount(): int;

    public function getNeededCrewCount(): int;

    public function getExcessCrewCount(): int;

    public function hasEnoughCrew(?GameControllerInterface $game = null): bool;

    public function getFleet(): ?FleetInterface;

    public function setFleet(?FleetInterface $fleet): ShipInterface;

    public function isFleetLeader(): bool;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ShipInterface;

    public function getSystem(): ?StarSystemInterface;

    public function getModules(): array;

    public function isDeflectorHealthy(): bool;

    public function isTroopQuartersHealthy(): bool;

    public function isMatrixScannerHealthy(): bool;

    public function isTorpedoStorageHealthy(): bool;

    public function isShuttleRampHealthy(): bool;

    public function isWebEmitterHealthy(): bool;

    public function isWarpAble(): bool;

    public function isTractoring(): bool;

    public function isTractored(): bool;

    public function isOverColony(): ?ColonyInterface;

    public function isOverSystem(): ?StarSystemInterface;

    public function isOverWormhole(): bool;

    public function isWarpPossible(): bool;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function getTorpedoStorage(): ?TorpedoStorageInterface;

    public function setTorpedoStorage(?TorpedoStorageInterface $torpedoStorage): ShipInterface;

    /**
     * @return Collection<int, StorageInterface> Indexed by commodityId, ordered by commodityId
     */
    public function getStorage(): Collection;

    /**
     * @return Collection<int, ShipLogInterface> Ordered by id
     */
    public function getLogbook(): Collection;

    public function getTakeoverActive(): ?ShipTakeoverInterface;

    public function setTakeoverActive(?ShipTakeoverInterface $takeover): ShipInterface;

    public function getTakeoverPassive(): ?ShipTakeoverInterface;

    public function setTakeoverPassive(?ShipTakeoverInterface $takeover): ShipInterface;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    /**
     * @return array<int, StorageInterface> Indexed by commodityId, ordered by commodityId
     */
    public function getBeamableStorage(): array;

    public function updateLocation(MapInterface|StarSystemMapInterface|Location $location): ShipInterface;

    public function getTradePost(): ?TradePostInterface;

    public function setTradePost(?TradePostInterface $tradePost): ShipInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): ShipInterface;

    public function getMapRegion(): ?MapRegionInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): ShipInterface;

    public function getLocation(): Location;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function setInfluenceArea(?StarSystemInterface $influenceArea): ShipInterface;

    public function getBeamFactor(): int;

    /**
     * return "x|y (System-Name)"
     */
    public function getSectorString(): string;

    public function getSectorId(): ?int;

    public function getBuildplan(): ?ShipBuildplanInterface;

    public function setBuildplan(?ShipBuildplanInterface $shipBuildplan): ShipInterface;

    /**
     * @return Collection<int, ShipSystemInterface>
     */
    public function getSystems(): Collection;

    public function hasShipSystem(ShipSystemTypeEnum $type): bool;

    public function getShipSystem(ShipSystemTypeEnum $type): ShipSystemInterface;

    /**
     * @return ShipSystemInterface[]
     */
    public function getHealthySystems(): array;

    public function displayNbsActions(): bool;

    public function isTractorbeamPossible(): bool;

    public function isBoardingPossible(): bool;

    public function isInterceptAble(): bool;

    public function getMapCX(): int;

    public function getMapCY(): int;

    public function dockedOnTradePost(): bool;

    /**
     * @return Collection<int, DockingPrivilegeInterface>
     */
    public function getDockPrivileges(): Collection;

    public function hasFreeDockingSlots(): bool;

    public function getDockingSlotCount(): int;

    public function getFreeDockingSlotCount(): int;

    public function getDockedShipCount(): int;

    public function getTractoredShip(): ?ShipInterface;

    public function setTractoredShip(?ShipInterface $ship): ShipInterface;

    public function setTractoredShipId(?int $shipId): ShipInterface;

    public function getTractoringShip(): ?ShipInterface;

    public function setTractoringShip(?ShipInterface $ship): ShipInterface;

    public function getHoldingWeb(): ?TholianWebInterface;

    public function setHoldingWeb(?TholianWebInterface $web): ShipInterface;

    public function getHoldingWebBackgroundStyle(): string;

    public function getCurrentMapField(): StarSystemMapInterface|MapInterface;

    public function getShieldRegenerationRate(): int;

    public function canIntercept(): bool;

    public function canMove(): bool;

    public function hasActiveWeapon(): bool;

    public function hasEscapePods(): bool;

    public function getRepairRate(): int;

    public function getRump(): ShipRumpInterface;

    public function setRump(ShipRumpInterface $shipRump): ShipInterface;

    public function getRumpId(): int;

    public function getRumpName(): string;

    public function hasPhaser(): bool;

    public function hasTorpedo(): bool;

    public function hasCloak(): bool;

    public function hasTachyonScanner(): bool;

    public function hasShuttleRamp(): bool;

    public function hasSubspaceScanner(): bool;

    public function hasAstroLaboratory(): bool;

    public function hasWarpdrive(): bool;

    public function hasReactor(): bool;

    public function hasRPGModule(): bool;

    public function hasNbsLss(): bool;

    public function hasUplink(): bool;

    public function hasTranswarp(): bool;

    public function getTranswarpCooldown(): ?int;

    public function getMaxTorpedos(): int;

    /**
     * @return Collection<int, ShipInterface>
     */
    public function getDockedShips(): Collection;

    public function getDockedTo(): ?ShipInterface;

    public function setDockedTo(?ShipInterface $dockedTo): ShipInterface;

    public function setDockedToId(?int $dockedToId): ShipInterface;

    public function hasFreeShuttleSpace(?LoggerUtilInterface $loggerUtil): bool;

    public function getStoredShuttles(): array;

    public function getStoredShuttleCount(): int;

    /**
     * @return CommodityInterface[]
     */
    public function getStoredBuoy(): array;

    public function hasStoredBuoy(): bool;

    public function canMan(): bool;

    public function canBuildConstruction(): bool;

    public function hasCrewmanOfUser(int $userId): bool;

    public function getHullColorStyle(): string;

    public function getIsInEmergency(): bool;

    public function setIsInEmergency(bool $inEmergency): ShipInterface;

    public function getDockedWorkbeeCount(): int;
}
