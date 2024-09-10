<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use RuntimeException;
use Stu\Component\Ship\Repair\RepairUtilInterface;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipStateEnum;
use Stu\Component\Ship\System\Data\AbstractSystemData;
use Stu\Component\Ship\System\Data\AstroLaboratorySystemData;
use Stu\Component\Ship\System\Data\BussardCollectorSystemData;
use Stu\Component\Ship\System\Data\EpsSystemData;
use Stu\Component\Ship\System\Data\FusionCoreSystemData;
use Stu\Component\Ship\System\Data\HullSystemData;
use Stu\Component\Ship\System\Data\ProjectileLauncherSystemData;
use Stu\Component\Ship\System\Data\ShieldSystemData;
use Stu\Component\Ship\System\Data\SingularityCoreSystemData;
use Stu\Component\Ship\System\Data\TrackerSystemData;
use Stu\Component\Ship\System\Data\WarpCoreSystemData;
use Stu\Component\Ship\System\Data\WarpDriveSystemData;
use Stu\Component\Ship\System\Data\WebEmitterSystemData;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Ship\Lib\Ui\StateIconAndTitle;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\ShipSystemInterface;
use Stu\Orm\Entity\ShipTakeoverInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

//TODO increase coverage
final class ShipWrapper implements ShipWrapperInterface
{
    /**
     * @var Collection<int, AbstractSystemData>
     */
    private Collection $shipSystemDataCache;

    private ?ReactorWrapperInterface $reactorWrapper = null;

    private ?int $epsUsage = null;

    public function __construct(
        private ShipInterface $ship,
        private ShipSystemManagerInterface $shipSystemManager,
        private SystemDataDeserializerInterface $systemDataDeserializer,
        private ColonyLibFactoryInterface $colonyLibFactory,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private GameControllerInterface $game,
        private ShipWrapperFactoryInterface $shipWrapperFactory,
        private ShipStateChangerInterface $shipStateChanger,
        private RepairUtilInterface $repairUtil,
        private StateIconAndTitle $stateIconAndTitle,
    ) {

        $this->shipSystemDataCache = new ArrayCollection();
    }

    #[Override]
    public function get(): ShipInterface
    {
        return $this->ship;
    }

    #[Override]
    public function getShipWrapperFactory(): ShipWrapperFactoryInterface
    {
        return $this->shipWrapperFactory;
    }

    #[Override]
    public function getShipSystemManager(): ShipSystemManagerInterface
    {
        return $this->shipSystemManager;
    }

    #[Override]
    public function getFleetWrapper(): ?FleetWrapperInterface
    {
        if ($this->ship->getFleet() === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapFleet($this->ship->getFleet());
    }

    #[Override]
    public function getEpsUsage(): int
    {
        if ($this->epsUsage === null) {
            $this->epsUsage = $this->reloadEpsUsage();
        }
        return $this->epsUsage;
    }

    #[Override]
    public function lowerEpsUsage(int $value): void
    {
        $this->epsUsage -= $value;
    }

    private function reloadEpsUsage(): int
    {
        $result = 0;

        foreach ($this->shipSystemManager->getActiveSystems($this->ship) as $shipSystem) {
            $result += $this->shipSystemManager->getEnergyConsumption($shipSystem->getSystemType());
        }

        if ($this->ship->getAlertState() == ShipAlertStateEnum::ALERT_YELLOW) {
            $result += ShipStateChangerInterface::ALERT_YELLOW_EPS_USAGE;
        }
        if ($this->ship->getAlertState() == ShipAlertStateEnum::ALERT_RED) {
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

    #[Override]
    public function getReactorWrapper(): ?ReactorWrapperInterface
    {
        if ($this->reactorWrapper === null) {
            $ship = $this->ship;
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

    #[Override]
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
    #[Override]
    public function getDamagedSystems(): array
    {
        $damagedSystems = [];
        $prioArray = [];
        foreach ($this->ship->getSystems() as $system) {
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

    #[Override]
    public function isOwnedByCurrentUser(): bool
    {
        return $this->game->getUser() === $this->ship->getUser();
    }

    #[Override]
    public function canLandOnCurrentColony(): bool
    {
        if ($this->ship->getRump()->getCommodity() === null) {
            return false;
        }
        if ($this->ship->isShuttle()) {
            return false;
        }

        $currentColony = $this->ship->getStarsystemMap() !== null ? $this->ship->getStarsystemMap()->getColony() : null;

        if ($currentColony === null) {
            return false;
        }
        if ($currentColony->getUser() !== $this->ship->getUser()) {
            return false;
        }

        return $this->colonyLibFactory
            ->createColonySurface($currentColony)
            ->hasAirfield();
    }

    #[Override]
    public function canBeRepaired(): bool
    {
        if ($this->ship->getAlertState() !== ShipAlertStateEnum::ALERT_GREEN) {
            return false;
        }

        if ($this->ship->getShieldState()) {
            return false;
        }

        if ($this->ship->getCloakState()) {
            return false;
        }

        if ($this->getDamagedSystems() !== []) {
            return true;
        }

        return $this->ship->getHull() < $this->ship->getMaxHull();
    }

    #[Override]
    public function canFire(): bool
    {
        $ship = $this->ship;
        if (!$ship->getNbs()) {
            return false;
        }
        if (!$ship->hasActiveWeapon()) {
            return false;
        }

        $epsSystem = $this->getEpsSystemData();
        return $epsSystem !== null && $epsSystem->getEps() !== 0;
    }

    #[Override]
    public function getRepairDuration(): int
    {
        return $this->repairUtil->getRepairDuration($this);
    }

    #[Override]
    public function getRepairDurationPreview(): int
    {
        return $this->repairUtil->getRepairDurationPreview($this);
    }

    #[Override]
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

    #[Override]
    public function getPossibleTorpedoTypes(): array
    {
        if ($this->ship->hasShipSystem(ShipSystemTypeEnum::SYSTEM_TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        }

        return $this->torpedoTypeRepository->getByLevel($this->ship->getRump()->getTorpedoLevel());
    }

    #[Override]
    public function getTractoredShipWrapper(): ?ShipWrapperInterface
    {
        $tractoredShip = $this->ship->getTractoredShip();
        if ($tractoredShip === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($tractoredShip);
    }

    #[Override]
    public function getTractoringShipWrapper(): ?ShipWrapperInterface
    {
        $tractoringShip = $this->ship->getTractoringShip();
        if ($tractoringShip === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($tractoringShip);
    }

    #[Override]
    public function getDockedToShipWrapper(): ?ShipWrapperInterface
    {
        $dockedTo = $this->ship->getDockedTo();
        if ($dockedTo === null) {
            return null;
        }

        return $this->shipWrapperFactory->wrapShip($dockedTo);
    }

    #[Override]
    public function getStateIconAndTitle(): ?array
    {
        return $this->stateIconAndTitle->getStateIconAndTitle($this);
    }

    #[Override]
    public function getTakeoverTicksLeft(?ShipTakeoverInterface $takeover = null): int
    {
        $takeover ??= $this->ship->getTakeoverActive();
        if ($takeover === null) {
            throw new RuntimeException('should not call when active takeover is null');
        }

        $currentTurn = $this->game->getCurrentRound()->getTurn();

        return $takeover->getStartTurn() + ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER - $currentTurn;
    }

    #[Override]
    public function canBeScrapped(): bool
    {
        $ship = $this->ship;

        return $ship->isBase() && $ship->getState() !== ShipStateEnum::SHIP_STATE_UNDER_SCRAPPING;
    }

    #[Override]
    public function getCrewStyle(): string
    {
        $ship = $this->ship;
        $excessCrew = $ship->getExcessCrewCount();

        if ($excessCrew === 0) {
            return "";
        }

        return $excessCrew > 0 ? "color: green;" : "color: red;";
    }

    #[Override]
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

    #[Override]
    public function getShieldSystemData(): ?ShieldSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_SHIELDS,
            ShieldSystemData::class
        );
    }

    #[Override]
    public function getEpsSystemData(): ?EpsSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_EPS,
            EpsSystemData::class
        );
    }

    #[Override]
    public function getWarpDriveSystemData(): ?WarpDriveSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            WarpDriveSystemData::class
        );
    }

    #[Override]
    public function getTrackerSystemData(): ?TrackerSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_TRACKER,
            TrackerSystemData::class
        );
    }

    #[Override]
    public function getBussardCollectorSystemData(): ?BussardCollectorSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_BUSSARD_COLLECTOR,
            BussardCollectorSystemData::class
        );
    }

    #[Override]
    public function getWebEmitterSystemData(): ?WebEmitterSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_THOLIAN_WEB,
            WebEmitterSystemData::class
        );
    }

    #[Override]
    public function getAstroLaboratorySystemData(): ?AstroLaboratorySystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_ASTRO_LABORATORY,
            AstroLaboratorySystemData::class
        );
    }

    #[Override]
    public function getProjectileLauncherSystemData(): ?ProjectileLauncherSystemData
    {
        return $this->getSpecificShipSystem(
            ShipSystemTypeEnum::SYSTEM_TORPEDO,
            ProjectileLauncherSystemData::class
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
        return $this->systemDataDeserializer->getSpecificShipSystem(
            $this->ship,
            $systemType,
            $className,
            $this->shipSystemDataCache,
            $this->shipWrapperFactory
        );
    }
}
