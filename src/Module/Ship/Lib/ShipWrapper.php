<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use JsonMapper\JsonMapperInterface;
use Stu\Component\Ship\Repair\CancelRepairInterface;
use Stu\Component\Ship\RepairTaskEnum;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Type\EpsShipSystem;
use Stu\Component\Ship\System\Type\HullShipSystem;
use Stu\Component\Ship\System\Type\ProjectileWeaponShipSystem;
use Stu\Component\Ship\System\Type\ShieldShipSystem;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\ShipSystemRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

final class ShipWrapper implements ShipWrapperInterface
{
    private ShipInterface $ship;

    private ShipSystemManagerInterface $shipSystemManager;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemRepositoryInterface $shipSystemRepository;

    private ColonyLibFactoryInterface $colonyLibFactory;

    private CancelRepairInterface $cancelRepair;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private GameControllerInterface $game;

    private JsonMapperInterface $jsonMapper;

    private $shipSystemCache = [];

    private $epsUsage;

    private $effectiveEpsProduction;

    public function __construct(
        ShipInterface $ship,
        ShipSystemManagerInterface $shipSystemManager,
        ShipRepositoryInterface $shipRepository,
        ShipSystemRepositoryInterface $shipSystemRepository,
        ColonyLibFactoryInterface $colonyLibFactory,
        CancelRepairInterface $cancelRepair,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        GameControllerInterface $game,
        JsonMapperInterface $jsonMapper
    ) {
        $this->ship = $ship;
        $this->shipSystemManager = $shipSystemManager;
        $this->shipRepository = $shipRepository;
        $this->shipSystemRepository = $shipSystemRepository;
        $this->colonyLibFactory = $colonyLibFactory;
        $this->cancelRepair = $cancelRepair;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->game = $game;
        $this->jsonMapper = $jsonMapper;
    }

    public function get(): ShipInterface
    {
        return $this->ship;
    }

    public function getEpsUsage(): int
    {
        if ($this->epsUsage === null) {
            $this->reloadEpsUsage();
        }
        return $this->epsUsage;
    }

    public function lowerEpsUsage($value): void
    {
        $this->epsUsage -= $value;
    }

    private function reloadEpsUsage(): void
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

        $this->epsUsage = $result;
    }

    public function getEffectiveEpsProduction(): int
    {
        if ($this->effectiveEpsProduction === null) {
            $eps = $this->getEpsShipSystem();

            $prod = $this->get()->getReactorOutputCappedByReactorLoad() - $this->getEpsUsage();
            if ($prod <= 0) {
                return $prod;
            }
            if ($eps->getEps() + $prod > $eps->getMaxEps()) {
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

    public function setAlertState(int $alertState, &$msg): void
    {
        $eps = $this->getEpsShipSystem();

        //check if enough energy
        if (
            $alertState == ShipAlertStateEnum::ALERT_YELLOW
            && $this->get()->getAlertState() == ShipAlertStateEnum::ALERT_GREEN
        ) {
            if ($eps->getEps() < 1) {
                throw new InsufficientEnergyException(1);
            }
            $eps->setEps($eps->getEps() - 1)->update();
        }
        if (
            $alertState == ShipAlertStateEnum::ALERT_RED
            && $this->get()->getAlertState() !== ShipAlertStateEnum::ALERT_RED
        ) {
            if ($eps->getEps() < 2) {
                throw new InsufficientEnergyException(2);
            }
            $eps->setEps($eps->getEps() - 2)->update();
        }

        // cancel repair if not on alert green
        if ($alertState !== ShipAlertStateEnum::ALERT_GREEN) {
            if ($this->cancelRepair->cancelRepair($this->get())) {
                $msg = _('Die Reparatur wurde abgebrochen');
            }
        }

        // now change
        $this->get()->setAlertState($alertState);
        $this->reloadEpsUsage();
    }

    public function leaveFleet(): void
    {
        $fleet = $this->get()->getFleet();

        if ($fleet !== null) {
            $fleet->getShips()->removeElement($this);

            $this->get()->setFleet(null);
            $this->get()->setIsFleetLeader(false);
            $this->get()->setFleetId(null);

            $this->shipRepository->save($this->get());
        }
    }

    //highest damage first, then prio
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
        if (!$this->get()->getRump()->getCommodityId()) {
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

        return $this->get()->getHull() < $this->get()->getMaxHuell();
    }

    public function getRepairDuration(): int
    {
        $ticks = (int) ceil(($this->get()->getMaxHuell() - $this->get()->getHull()) / $this->get()->getRepairRate());
        $ticks = max($ticks, (int) ceil(count($this->getDamagedSystems()) / 2));

        return $ticks;
    }

    public function getRepairCosts(): array
    {
        $neededSpareParts = 0;
        $neededSystemComponents = 0;

        $hull = $this->get()->getHull();
        $maxHull = $this->get()->getMaxHuell();

        if ($hull < $maxHull) {
            $ticks = (int) ceil(($this->get()->getMaxHuell() - $this->get()->getHull()) / $this->get()->getRepairRate());
            $neededSpareParts += ((int)($this->get()->getRepairRate() / RepairTaskEnum::HULL_HITPOINTS_PER_SPARE_PART)) * $ticks;
        }

        $damagedSystems = $this->getDamagedSystems();
        foreach ($damagedSystems as $system) {
            $systemLvl = $this->determinSystemLevel($system);
            $healingPercentage = (100 - $system->getStatus()) / 100;

            $neededSpareParts += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SPARE_PARTS_ONLY]);
            $neededSystemComponents += (int)ceil($healingPercentage * RepairTaskEnum::SHIPYARD_PARTS_USAGE[$systemLvl][RepairTaskEnum::SYSTEM_COMPONENTS_ONLY]);
        }

        return [
            new ShipRepairCost($neededSpareParts, CommodityTypeEnum::COMMODITY_SPARE_PART, CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SPARE_PART)),
            new ShipRepairCost($neededSystemComponents, CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT, CommodityTypeEnum::getDescription(CommodityTypeEnum::COMMODITY_SYSTEM_COMPONENT))
        ];
    }

    private function determinSystemLevel(ShipSystemInterface $system): int
    {
        $module = $system->getModule();

        if ($module !== null) {
            return $module->getLevel();
        } else {
            return $system->getShip()->getRump()->getModuleLevel();
        }
    }

    public function getPossibleTorpedoTypes(): array
    {
        if ($this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        }

        return $this->torpedoTypeRepository->getByLevel($this->ship->getRump()->getTorpedoLevel());
    }

    public function getHullShipSystem(): HullShipSystem
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_HULL,
            new HullShipSystem($this->shipSystemRepository)
        );
    }

    public function getShieldShipSystem(): ?ShieldShipSystem
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_SHIELDS,
            new ShieldShipSystem($this->cancelRepair)
        );
    }

    public function getEpsShipSystem(): ?EpsShipSystem
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_EPS,
            new EpsShipSystem($this->shipSystemRepository)
        );
    }

    public function getProjectileWeaponShipSystem(): ?ProjectileWeaponShipSystem
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_TORPEDO,
            new ProjectileWeaponShipSystem()
        );
    }

    /**
     * @param ShipSystemInterface $object
     */
    private function getSpecificShipSystem(int $systemId, $object)
    {
        //add system to cache if not already deserialized
        if (!array_key_exists($systemId, $this->shipSystemCache)) {
            if ($systemId === ShipSystemTypeEnum::SYSTEM_HULL) {
                $data = null;
            } else {

                if (!$this->get()->hasShipSystem($systemId)) {
                    return null;
                }


                $data = $this->get()->getShipSystem($systemId)->getData();
            }

            if ($data === null) {
                $this->shipSystemCache[$systemId] = $object;
            } else {
                $object->setShip($this->get());

                $this->shipSystemCache[$systemId] =
                    $this->jsonMapper->mapObjectFromString(
                        $data,
                        $object
                    );
            }
        }

        //return deserialized system
        return $this->shipSystemCache[$systemId];
    }
}
