<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperInterface;
use RuntimeException;
use Stu\Component\Building\BuildingEnum;
use Stu\Component\Colony\ColonyFunctionManagerInterface;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\AbstractSystemData;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ShieldSystemData;
use Stu\Component\Ship\System\Data\ShipSystemDataFactoryInterface;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\Data\WebEmitterSystemData;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ColonyShipRepairRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

//TODO increase coverage
final class ShipWrapper implements ShipWrapperInterface
{
    private ShipInterface $ship;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private ColonyShipRepairRepositoryInterface $colonyShipRepairRepository;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private ShipSystemDataFactoryInterface $shipSystemDataFactory;

    private ColonyFunctionManagerInterface $colonyFunctionManager;

    private ShipStateChangerInterface $shipStateChanger;

    /**
     * @var array<int, AbstractSystemData>
     */
    private $shipSystemDataCache = [];

    private ?int $epsUsage = null;

    private ?int $effectiveEpsProduction = null;

    public function __construct(
        ColonyFunctionManagerInterface $colonyFunctionManager,
        ShipInterface $ship,
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        ColonyShipRepairRepositoryInterface $colonyShipRepairRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        ShipSystemDataFactoryInterface $shipSystemDataFactory,
        ShipStateChangerInterface $shipStateChanger
    ) {
        $this->ship = $ship;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->colonyShipRepairRepository = $colonyShipRepairRepository;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->shipSystemDataFactory = $shipSystemDataFactory;
        $this->colonyFunctionManager = $colonyFunctionManager;
        $this->shipStateChanger = $shipStateChanger;
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
            $result += ShipAlertStateEnum::ALERT_YELLOW_EPS_USAGE;
        }
        if ($this->get()->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
            $result += ShipAlertStateEnum::ALERT_RED_EPS_USAGE;
        }

        return $result;
    }

    public function getEffectiveEpsProduction(): int
    {
        if ($this->effectiveEpsProduction === null) {
            $prod = $this->get()->getReactorOutputCappedByReactorLoad() - $this->getEpsUsage();
            if ($prod <= 0) {
                return $prod;
            }

            $eps = $this->getEpsSystemData();
            if (
                $eps !== null
                && $eps->getEps() + $prod > $eps->getMaxEps()
            ) {
                return $eps->getMaxEps() - $eps->getEps();
            }
            $this->effectiveEpsProduction = $prod;
        }
        return $this->effectiveEpsProduction;
    }

    public function getWarpcoreUsage(): int
    {
        return $this->getEffectiveEpsProduction() + $this->getEpsUsage();
    }

    public function setAlertState(int $alertState): ?string
    {
        $msg = $this->shipStateChanger->changeAlertState($this, $alertState);
        $this->epsUsage = $this->reloadEpsUsage();

        return $msg;
    }

    public function leaveFleet(): void
    {
        $fleet = $this->get()->getFleet();

        if ($fleet !== null) {
            $fleet->getShips()->removeElement($this->get());

            $this->get()->setFleet(null);
            $this->get()->setIsFleetLeader(false);
            $this->get()->setFleetId(null);

            $this->shipRepository->save($this->get());
        }
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
                $prioArray[$system->getSystemType()] = $this->shipSystemManager->lookupSystem($system->getSystemType())->getPriority();
            }
        }

        // sort by damage and priority
        usort(
            $damagedSystems,
            function (ShipSystemInterface $a, ShipSystemInterface $b) use ($prioArray): int {
                if ($a->getStatus() == $b->getStatus()) {
                    if ($prioArray[$a->getSystemType()] == $prioArray[$b->getSystemType()]) {
                        return 0;
                    }
                    return ($prioArray[$a->getSystemType()] > $prioArray[$b->getSystemType()]) ? -1 : 1;
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
        $ship = $this->get();

        $ticks = $this->getRepairTicks($ship);

        //check if repair station is active
        $colonyRepair = $this->colonyShipRepairRepository->getByShip($ship->getId());
        if ($colonyRepair !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colonyRepair->getColony(), BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    public function getRepairDurationPreview(): int
    {
        $ship = $this->get();

        $ticks = $this->getRepairTicks($ship);

        $colony = $ship->isOverColony();
        if ($colony !== null) {
            $isRepairStationBonus = $this->colonyFunctionManager->hasActiveFunction($colony, BuildingEnum::BUILDING_FUNCTION_REPAIR_SHIPYARD);
            if ($isRepairStationBonus) {
                $ticks = (int)ceil($ticks / 2);
            }
        }

        return $ticks;
    }

    private function getRepairTicks(ShipInterface $ship): int
    {
        $ticks = (int) ceil(($ship->getMaxHull() - $ship->getHull()) / $this->get()->getRepairRate());
        return max($ticks, (int) ceil(count($this->getDamagedSystems()) / 2));
    }

    public function getRepairCosts(): array
    {
        $neededSpareParts = 0;
        $neededSystemComponents = 0;

        $hull = $this->get()->getHull();
        $maxHull = $this->get()->getMaxHull();

        if ($hull < $maxHull) {
            $ticks = (int) ceil(($this->get()->getMaxHull() - $this->get()->getHull()) / $this->get()->getRepairRate());
            $neededSpareParts += ((int)($this->get()->getRepairRate() / RepairTaskEnum::HULL_HITPOINTS_PER_SPARE_PART)) * $ticks;
        }

        $damagedSystems = $this->getDamagedSystems();
        foreach ($damagedSystems as $system) {
            $systemLvl = $system->determineSystemLevel();
            $healingPercentage = (100 - $system->getStatus()) / 100;

            $neededSpareParts += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SPARE_PARTS_ONLY]);
            $neededSystemComponents += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SYSTEM_COMPONENTS_ONLY]);
        }

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

    public function getStateIconAndTitle(): ?array
    {
        $state = $this->get()->getState();
        $isBase = $this->get()->isBase();
        $repairDuration = $this->getRepairDuration();

        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_PASSIVE) {
            return ['rep2', sprintf('%s wird repariert (noch %s Runden)', $isBase ? 'Station' : 'Schiff', $repairDuration)];
        }
        if ($state === ShipStateEnum::SHIP_STATE_REPAIR_ACTIVE) {
            return ['rep2', sprintf('%s repariert die Station', $isBase ? 'Stationscrew' : 'Schiffscrew')];
        }
        if ($state === ShipStateEnum::SHIP_STATE_SYSTEM_MAPPING) {
            return ['map1', 'Schiff kartographiert'];
        }

        return null;
    }

    public function canBeScrapped(): bool
    {
        $ship = $this->get();

        return $ship->isBase() && $ship->getState() !== ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING;
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
    private function getSpecificShipSystem(int $systemType, string $className)
    {
        if (
            $systemType !== ShipSystemTypeEnum::SYSTEM_HULL
            && !$this->get()->hasShipSystem($systemType)
        ) {
            return null;
        }

        //add system to cache if not already deserialized
        if (!array_key_exists($systemType, $this->shipSystemDataCache)) {
            $systemData = $this->shipSystemDataFactory->createSystemData($systemType, $this->shipWrapperFactory);
            $systemData->setShip($this->get());

            if ($systemType === ShipSystemTypeEnum::SYSTEM_HULL) {
                $data = null;
            } else {
                $data = $this->get()->getShipSystem($systemType)->getData();
            }

            if ($data === null) {
                $this->shipSystemDataCache[$systemType] = $systemData;
            } else {
                $this->shipSystemDataCache[$systemType] =
                    $this->jsonMapper->mapObjectFromString(
                        $data,
                        $systemData
                    );
            }
        }

        //load deserialized system from cache
        $cacheItem = $this->shipSystemDataCache[$systemType];
        if (!$cacheItem instanceof $className) {
            throw new RuntimeException('this should not happen');
        }

        return $cacheItem;
    }
}
