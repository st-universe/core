<?php

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\Collection;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Logging\LoggerUtilInterface;

interface ShipInterface
{
    public function getId(): ?int;

    /**
     * @deprecated
     */
    public function getUserId(): int;

    public function getUserName(): string;

    public function getFleetId(): ?int;

    public function setFleetId(?int $fleetId): ShipInterface;

    public function getSystemsId(): ?int;

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

    public function getAlertState(): int;

    public function setAlertState(int $alvl): ShipInterface;

    public function setAlertStateGreen(): ShipInterface;

    public function isSystemHealthy(int $systemId): bool;

    public function getSystemState(int $systemId): bool;

    public function getImpulseState(): bool;

    public function getWarpState(): bool;

    public function getReactorLoad(): int;

    public function setReactorLoad(int $reactorload): ShipInterface;

    public function getCloakState(): bool;

    public function getTachyonState(): bool;

    public function getSubspaceState(): bool;

    public function getAstroState(): bool;

    public function getConstructionHubState(): bool;

    public function getEps(): int;

    public function setEps(int $eps): ShipInterface;

    public function getMaxEps(): int;

    public function getTheoreticalMaxEps(): int;

    public function setMaxEps(int $maxEps): ShipInterface;

    public function getEBatt(): int;

    public function setEBatt(int $batt): ShipInterface;

    public function getMaxEBatt(): int;

    public function setMaxEBatt(): ShipInterface;

    public function getHuell(): int;

    public function setHuell(int $hull): ShipInterface;

    public function getMaxHuell(): int;

    public function setMaxHuell(int $maxHull): ShipInterface;

    public function getShield(): int;

    public function setShield(int $schilde): ShipInterface;

    public function getMaxShield(): int;

    public function setMaxShield(int $maxShields): ShipInterface;

    public function getShieldState(): bool;

    public function getNbs(): bool;

    public function getLss(): bool;

    public function getPhaserState(): bool;

    public function isAlertGreen(): bool;

    public function getTorpedoState(): bool;

    public function getFormerRumpId(): int;

    public function setFormerRumpId(int $formerShipRumpId): ShipInterface;

    public function getTorpedoCount(): int;

    public function getEBattWaitingTime(): int;

    public function setEBattWaitingTime(int $batteryCooldown): ShipInterface;

    public function isBase(): bool;

    public function isTrumfield(): bool;

    public function isShuttle(): bool;

    public function isConstruction(): bool;

    public function setIsBase(bool $isBase): ShipInterface;

    public function getDatabaseId(): int;

    public function setDatabaseId(int $databaseEntryId): ShipInterface;

    public function getIsDestroyed(): bool;

    public function setIsDestroyed(bool $isDestroyed): ShipInterface;

    public function getDisabled(): bool;

    public function setDisabled(bool $disabled): ShipInterface;

    public function getCanBeDisabled(): bool;

    public function getHitChance(): int;

    public function setHitChance(int $hitChance): ShipInterface;

    public function getEvadeChance(): int;

    public function setEvadeChance(int $evadeChance): ShipInterface;

    public function getReactorOutput(): int;

    public function getTheoreticalReactorOutput(): int;

    public function setReactorOutput(int $reactorOutput): ShipInterface;

    public function getBaseDamage(): int;

    public function setBaseDamage(int $baseDamage): ShipInterface;

    public function getSensorRange(): int;

    public function setSensorRange(int $sensorRange): ShipInterface;

    public function getTractorPayload(): int;

    public function getShieldRegenerationTimer(): int;

    public function setShieldRegenerationTimer(int $shieldRegenerationTimer): ShipInterface;

    public function getState(): int;

    public function setState(int $state): ShipInterface;

    public function getAstroStartTurn(): ?int;

    public function setAstroStartTurn(?int $turn): ShipInterface;

    public function getIsFleetLeader(): bool;

    public function setIsFleetLeader(bool $isFleetLeader): ShipInterface;

    /**
     * @return ShipCrewInterface[]|Collection
     */
    public function getCrewlist(): Collection;

    public function getPosX(): int;

    public function getPosY(): int;

    public function getCrewCount(): int;

    public function getMaxCrewCount(): int;

    public function getExcessCrewCount(): int;

    public function hasEnoughCrew(?GameControllerInterface $game = null): bool;

    public function getFleet(): ?FleetInterface;

    public function setFleet(?FleetInterface $fleet): ShipInterface;

    public function isFleetLeader(): bool;

    public function getUser(): UserInterface;

    public function setUser(UserInterface $user): ShipInterface;

    public function getSystem(): ?StarSystemInterface;

    public function getModules(): array;

    public function getReactorCapacity(): int;

    public function getReactorOutputCappedByReactorLoad(): int;

    public function isWarpcoreHealthy(): bool;

    public function isDeflectorHealthy(): bool;

    public function isTroopQuartersHealthy(): bool;

    public function isMatrixScannerHealthy(): bool;

    public function isTorpedoStorageHealthy(): bool;

    public function isShuttleRampHealthy(): bool;

    public function isEBattUseable(): bool;

    public function isWarpAble(): bool;

    public function isTractoring(): bool;

    public function isTractored(): bool;

    public function isOverSystem(): ?StarSystemInterface;

    public function isOverWormhole(): bool;

    public function isWarpPossible(): bool;

    public function getTorpedo(): ?TorpedoTypeInterface;

    public function getTorpedoStorage(): ?TorpedoStorageInterface;

    public function setTorpedoStorage(?TorpedoStorageInterface $torpedoStorage): ShipInterface;

    /**
     * @return StorageInterface[]|Collection Indexed by commodityId, ordered by commodityId
     */
    public function getStorage(): Collection;

    public function getStorageSum(): int;

    public function getMaxStorage(): int;

    /**
     * @return StorageInterface[]|Collection Indexed by commodityId, ordered by commodityId
     */
    public function getBeamableStorage(): array;

    public function updateLocation(?MapInterface $map, ?StarSystemMapInterface $starsystem_map): ShipInterface;

    public function getTradePost(): ?TradePostInterface;

    public function setTradePost(?TradePostInterface $tradePost): ShipInterface;

    public function getMap(): ?MapInterface;

    public function setMap(?MapInterface $map): ShipInterface;

    public function getStarsystemMap(): ?StarSystemMapInterface;

    public function setStarsystemMap(?StarSystemMapInterface $starsystem_map): ShipInterface;

    public function getInfluenceArea(): ?StarSystemInterface;

    public function setInfluenceArea(?StarSystemInterface $influenceArea): ShipInterface;

    public function getBeamFactor(): int;

    /**
     * return "x|y (System-Name)"
     */
    public function getSectorString(): string;

    public function getBuildplan(): ?ShipBuildplanInterface;

    public function setBuildplan(?ShipBuildplanInterface $shipBuildplan): ShipInterface;

    /**
     * @return ShipSystemInterface[]|Collection
     */
    public function getSystems(): Collection;

    public function hasShipSystem($system): bool;

    /**
     * @param ShipSystemTypeEnum
     */
    public function getShipSystem($system): ShipSystemInterface;

    /**
     * @return ShipSystemInterface[]
     */
    public function getHealthySystems(): array;

    public function displayNbsActions(): bool;

    public function tractorbeamNotPossible(): bool;

    public function isInterceptAble(): bool;

    public function getMapCX(): int;

    public function getMapCY(): int;

    public function dockedOnTradePost(): bool;

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

    /**
     * @return StarSystemMapInterface|MapInterface
     */
    public function getCurrentMapField();

    public function getShieldRegenerationRate(): int;

    public function canIntercept(): bool;

    public function canMove(): bool;

    public function canBeAttacked(bool $checkWarpState = true): bool;

    public function canAttack(): bool;

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

    public function hasWarpcore(): bool;

    public function hasWarpdrive(): bool;

    public function hasFusionReactor(): bool;

    public function hasNbsLss(): bool;

    public function hasUplink(): bool;

    public function hasTranswarp(): bool;

    public function getTranswarpCooldown(): ?int;

    public function getMaxTorpedos(): int;

    /**
     * @return ShipInterface[]|Collection
     */
    public function getDockedShips(): Collection;

    public function getDockedTo(): ?ShipInterface;

    public function setDockedTo(?ShipInterface $dockedTo): ShipInterface;

    public function setDockedToId(?int $dockedToId): ShipInterface;

    public function hasFreeShuttleSpace(?LoggerUtilInterface $loggerUtil): bool;

    public function getStoredShuttles(): array;

    public function getStoredShuttleCount(): int;

    public function canBuildConstruction(): bool;

    public function getHullStatusBar();

    public function getShieldStatusBar();

    public function getEpsStatusBar();

    public function getHullColorStyle(): string;
}
