<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JBBCode\Parser;
use JsonMapper\JsonMapperInterface;
use RuntimeException;
use Stu\Component\Ship\AstronomicalMappingEnum;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\AbstractSystemData;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\FusionCoreSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShieldSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\Data\SingularityCoreSystemData;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\Data\WarpCoreSystemData;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\Component\Ship\System\Data\WebEmitterSystemData;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

//TODO increase coverage
final class ShipWrapper implements ShipWrapperInterface
{
    private ShipInterface $ship;

    private ShipSystemManagerInterface $shipSystemManager;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private ShipStateChangerInterface $shipStateChanger;

    private RepairUtilInterface $repairUtil;

    private Parser $bbCodeParser;

    /**
     * @var array<int, AbstractSystemData>
     */
    private array $shipSystemDataCache = [];

    private ?ReactorWrapperInterface $reactorWrapper = null;

    private ?int $epsUsage = null;

    public function __construct(
        ShipInterface $ship,
        ShipSystemManagerInterface $shipSystemManager,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipSystemDataFactoryInterface $shipSystemDataFactory,
        ShipStateChangerInterface $shipStateChanger,
        RepairUtilInterface $repairUtil,
        Parser $bbCodeParser,
    ) {
        $this->ship = $ship;
        $this->shipSystemManager = $shipSystemManager;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->shipSystemDataFactory = $shipSystemDataFactory;
        $this->shipStateChanger = $shipStateChanger;
        $this->repairUtil = $repairUtil;
        $this->bbCodeParser = $bbCodeParser;
    }

    public function get(): ShipInterface
    {
        return $this->ship;
    }

    public function getShipWrapperFactory(): ShipWrapperFactoryInterface
    {
        return $this->shipWrapperFactory;
    }

    public function getShipSystemManager(): ShipSystemManagerInterface
    {
        return $this->shipSystemManager;
    }

    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        if ($this->get()->getFleet() === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapFleet($this->get()->getFleet());
    }

    public function getEpsUsage(): int
    {
        if ($this->epsUsage === null) {
            $this->epsUsage = $this->reloadEpsUsage();
        }
        return $this->epsUsage;
    }

    public function lowerEpsUsage(int $value): void
    {
        $this->epsUsage -= $value;
    }

    private function reloadEpsUsage(): int
    {
        $result = 0;

        foreach ($this->shipSystemManager->getActiveSystems($this->get()) as $shipSystem) {
            $result += $this->shipSystemManager->getEnergyConsumption($shipSystem->getSystemType());
        }

        if ($this->get()->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW) {
            $result += ShipStateChangerInterface::ALERT_YELLOW_EPS_USAGE;
        }
        if ($this->get()->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
            $result += ShipStateChangerInterface::ALERT_RED_EPS_USAGE;
        }

        return $result;
    }

    public function getReactorUsage(): int
    {
        $reactor = $this->reactorWrapper;
        if ($reactor === null) {
            throw new RuntimeException('this should not happen');
        }

        return $this->getEpsUsage() + $reactor->getUsage();
    }

    public function getReactorWrapper(): ?ReactorWrapperInterface
    {
        if ($this->reactorWrapper === null) {
            $ship = $this->get();
            $reactorSystemData = null;


            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_WARPCORE)) {
                $reactorSystemData = $this->getSpecificShipSystem(
                    ShipSystemTypeEnum::SYSTEM_WARPCORE,
                    WarpCoreSystemData::class
                );
            }
            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR)) {
                $reactorSystemData = $this->getSpecificShipSystem(
                    ShipSystemTypeEnum::SYSTEM_SINGULARITY_REACTOR,
                    SingularityCoreSystemData::class
                );
            }
            if ($ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR)) {
                $reactorSystemData = $this->getSpecificShipSystem(
                    ShipSystemTypeEnum::SYSTEM_FUSION_REACTOR,
                    FusionCoreSystemData::class
                );
            }

            if ($reactorSystemData === null) {
                return null;
            }

            $this->reactorWrapper = new ReactorWrapper($this, $reactorSystemData);
        }

        return $this->reactorWrapper;
    }

    public function setAlertState(ShipAlertStateEnum $alertState): ?string
    {
        $msg = $this->shipStateChanger->changeAlertState($this, $alertState);
        $this->epsUsage = $this->reloadEpsUsage();

        return $msg;
    }

    /**
     * highest damage first, then prio
     *
     * @return ShipSystemInterface[]
     */
    public function getDamagedSystems(): array
    {
        $damagedSystems = [];
        $prioArray = [];
        foreach ($this->get()->getSystems() as $system) {
            if ($system->getStatus() < 100) {
                $damagedSystems[] = $system;
                $prioArray[$system->getSystemType()->value] = $this->shipSystemManager->lookupSystem($system->getSystemType())->getPriority();
            }
        }

        // sort by damage and priority
        usort(
            $damagedSystems,
            function (ShipSystemInterface $a, ShipSystemInterface $b) use ($prioArray): int {
                if ($a->getStatus() === $b->getStatus()) {
                    return $prioArray[$b->getSystemType()->value] <=> $prioArray[$a->getSystemType()->value];
                }
                return ($a->getStatus() < $b->getStatus()) ? -1 : 1;
            }
        );

        return $damagedSystems;
    }

    public function isOwnedByCurrentUser(): bool
    {
        return $this->game->getUser() === $this->get()->getUser();
    }

    public function canLandOnCurrentColony(): bool
    {
        if ($this->get()->getRump()->getCommodity() === null) {
            return false;
        }
        if ($this->get()->isShuttle()) {
            return false;
        }

        $currentColony = $this->get()->getStarsystemMap() !== null ? $this->get()->getStarsystemMap()->getColony() : null;

        if ($currentColony === null) {
            return false;
        }
        if ($currentColony->getUser() !== $this->get()->getUser()) {
            return false;
        }

        return $this->colonyLibFactory
            ->createColonySurface($currentColony)
            ->hasAirfield();
    }

    public function canBeRepaired(): bool
    {
        if ($this->get()->getAlertState() !== ShipAlertStateEnum::ALERT_GREEN) {
            return false;
        }

        if ($this->get()->getShieldState()) {
            return false;
        }

        if ($this->get()->getCloakState()) {
            return false;
        }

        if (!empty($this->getDamagedSystems())) {
            return true;
        }

        return $this->get()->getHull() < $this->get()->getMaxHull();
    }

    public function getRepairDuration(): int
    {
        return $this->repairUtil->getRepairDuration($this);
    }

    public function getRepairDurationPreview(): int
    {
        return $this->repairUtil->getRepairDurationPreview($this);
    }

    public function getRepairCosts(): array
    {
        $neededParts = $this->repairUtil->determineSpareParts($this, false);

        $neededSpareParts = $neededParts[CommodityTypeEnum::COMMODITY_SPARE_PART];
        $neededSystemComponents = $neededParts[CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT];

        return [
            new ShipRepairCost($neededSpareParts, CommodityTypeEnum::COMMODITY_SPARE_PART, CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SPARE_PART)),
            new ShipRepairCost($neededSystemComponents, CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT, CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT))
        ];
    }

    public function getPossibleTorpedoTypes(): array
    {
        if ($this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        }

        return $this->torpedoTypeRepository->getByLevel($this->ship->getRump()->getTorpedoLevel());
    }

    public function getTractoredShipWrapper(): ?ShipWrapperInterface
    {
        $tractoredShip = $this->get()->getTractoredShip();
        if ($tractoredShip === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($tractoredShip);
    }

    public function getTractoringShipWrapper(): ?ShipWrapperInterface
    {
        $tractoringShip = $this->get()->getTractoringShip();
        if ($tractoringShip === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($tractoringShip);
    }

    public function getDockedToShipWrapper(): ?ShipWrapperInterface
    {
        $dockedTo = $this->get()->getDockedTo();
        if ($dockedTo === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($dockedTo);
    }

    public function getStateIconAndTitle(): ?array
    {
        $ship = $this->get();

        $state = $ship->getState();

        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE) {
            $isBase = $ship->isBase();
            return ['rep2', sprintf('%s repariert die Station', $isBase ? 'Stationscrew' : 'Schiffscrew')];
        }

        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            $isBase = $ship->isBase();
            $repairDuration = $this->getRepairDuration();
            return ['rep2', sprintf('%s wird repariert (noch %d Runden)', $isBase ? 'Station' : 'Schiff', $repairDuration)];
        }

        $currentTurn = $this->game->getCurrentRound()->getTurn();
        if ($state === ShipStateEnum::SHIP_STATE_ASTRO_FINALIZING) {
            return ['map1', sprintf(
                'Schiff kartographiert (noch %d Runden)',
                $ship->getAstroStartTurn() + AstronomicalMappingEnum::TURNS_TO_FINISH - $currentTurn
            )];
        }

        $takeover = $ship->getTakeoverActive();
        if (
            $state === ShipStateEnum::SHIP_STATE_ACTIVE_TAKEOVER
            && $takeover !== null
        ) {
            $targetNamePlainText = $this->bbCodeParser->parse($takeover->getTargetShip()->getName())->getAsText();
            return ['take2', sprintf(
                'Schiff übernimmt die "%s" (noch %d Runden)',
                $targetNamePlainText,
                $this->getTakeoverTicksLeft($takeover)
            )];
        }

        $takeover = $ship->getTakeoverPassive();
        if ($takeover !== null) {
            $sourceUserNamePlainText = $this->bbCodeParser->parse($takeover->getSourceShip()->getUser()->getName())->getAsText();
            return ['untake2', sprintf(
                'Schiff wird von Spieler "%s" übernommen (noch %d Runden)',
                $sourceUserNamePlainText,
                $this->getTakeoverTicksLeft($takeover)
            )];
        }

        return null;
    }

    public function getTakeoverTicksLeft(ShipTakeoverInterface $takeover = null): int
    {
        $takeover = $takeover ?? $this->get()->getTakeoverActive();
        if ($takeover === null) {
            throw new RuntimeException('should not call when active takeover is null');
        }

        $currentTurn = $this->game->getCurrentRound()->getTurn();

        return $takeover->getStartTurn() + ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER - $currentTurn;
    }

    public function canBeScrapped(): bool
    {
        $ship = $this->get();

        return $ship->isBase() && $ship->getState() !== ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING;
    }

    public function getCrewStyle(): string
    {
        $ship = $this->get();
        $excessCrew = $ship->getExcessCrewCount();

        if ($excessCrew === 0) {
            return "";
        }

        return $excessCrew > 0 ? "color: green;" : "color: red;";
    }

    public function getHullSystemData(): HullSystemData
    {
        $hullSystemData = $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_HULL,
            HullSystemData::class
        );

        if ($hullSystemData === null) {
            throw new SystemNotFoundException('no hull installed?');
        }

        return $hullSystemData;
    }

    public function getShieldSystemData(): ?ShieldSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_SHIELDS,
            ShieldSystemData::class
        );
    }

    public function getEpsSystemData(): ?EpsSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class
        );
    }

    public function getWarpDriveSystemData(): ?WarpDriveSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            WarpDriveSystemData::class
        );
    }

    public function getTrackerSystemData(): ?TrackerSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_TRACKER,
            TrackerSystemData::class
        );
    }

    public function getWebEmitterSystemData(): ?WebEmitterSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB,
            WebEmitterSystemData::class
        );
    }

    /**
     * @template T
     * @param class-string<T> $className
     *
     * @return T|null
     */
    private function getSpecificShipSystem(ShipSystemTypeEnum $systemType, string $className)
    {
        if (
            $systemType !== ShipSystemTypeEnum::SYSTEM_HULL
            && !$this->get()->hasShipSystem($systemType)
        ) {
            return null;
        }

        //add system to cache if not already deserialized
        if (!array_key_exists($systemType->value, $this->shipSystemDataCache)) {
            $systemData = $this->shipSystemDataFactory->createSystemData($systemType, $this->shipWrapperFactory);
            $systemData->setShip($this->get());

            $data = $systemType === ShipSystemTypeEnum::SYSTEM_HULL ? null : $this->get()->getShipSystem($systemType)->getData();

            if ($data === null) {
                $this->shipSystemDataCache[$systemType->value] = $systemData;
            } else {
                $this->shipSystemDataCache[$systemType->value] =
                    $this->jsonMapper->mapObjectFromString(
                        $data,
                        $systemData
                    );
            }
        }

        //load deserialized system from cache
        $cacheItem = $this->shipSystemDataCache[$systemType->value];
        if (!$cacheItem instanceof $className) {
            throw new RuntimeException('this should not happen');
        }

        return $cacheItem;
    }
}