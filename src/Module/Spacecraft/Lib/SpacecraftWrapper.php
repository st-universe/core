<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use BadMethodCallException;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Override;
use Stu\Component\Spacecraft\Repair\RepairUtilInterface;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Data\AbstractSystemData;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Spacecraft\System\SystemDataDeserializerInterface;
use Stu\Module\Colony\Lib\ColonyLibFactoryInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Interaction\ShipTakeoverManagerInterface;
use Stu\Module\Spacecraft\Lib\ReactorWrapperInterface;
use Stu\Module\Spacecraft\Lib\ShipRepairCost;
use Stu\Module\Spacecraft\Lib\SpacecraftStateChangerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Reactor\ReactorWrapperFactoryInterface;
use Stu\Module\Spacecraft\Lib\Ui\StateIconAndTitle;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Entity\ShipTakeover;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

//TODO increase coverage
/**
 * @template T of Spacecraft
 */
abstract class SpacecraftWrapper implements SpacecraftWrapperInterface
{
    use SpacecraftWrapperSystemDataTrait;

    /** @var Collection<int, AbstractSystemData> */
    private Collection $shipSystemDataCache;

    private ?ReactorWrapperInterface $reactorWrapper = null;

    private ?int $epsUsage = null;

    /**
     * @param T $spacecraft
     */
    public function __construct(
        protected readonly Spacecraft $spacecraft,
        private readonly SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private readonly SystemDataDeserializerInterface $systemDataDeserializer,
        private readonly TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        protected readonly GameControllerInterface $game,
        protected readonly SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory,
        private readonly ReactorWrapperFactoryInterface $reactorWrapperFactory,
        private readonly SpacecraftStateChangerInterface $spacecraftStateChanger,
        private readonly RepairUtilInterface $repairUtil,
        private readonly StateIconAndTitle $stateIconAndTitle,
        protected readonly ColonyLibFactoryInterface $colonyLibFactory
    ) {
        $this->shipSystemDataCache = new ArrayCollection();
    }

    #[Override]
    public function get(): Spacecraft
    {
        return $this->spacecraft;
    }

    #[Override]
    public function getSpacecraftWrapperFactory(): SpacecraftWrapperFactoryInterface
    {
        return $this->spacecraftWrapperFactory;
    }

    #[Override]
    public function getSpacecraftSystemManager(): SpacecraftSystemManagerInterface
    {
        return $this->spacecraftSystemManager;
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

        foreach ($this->spacecraftSystemManager->getActiveSystems($this->spacecraft) as $shipSystem) {
            $result += $this->spacecraftSystemManager->getEnergyConsumption($shipSystem->getSystemType());
        }

        return $this->get()->hasComputer()
            ? $result + $this->getComputerSystemDataMandatory()->getAlertState()->getEpsUsage()
            : $result;
    }

    public function getReactorUsage(): int
    {
        $reactor = $this->reactorWrapper;
        if ($reactor === null) {
            throw new BadMethodCallException('this should not happen');
        }

        return $this->getEpsUsage() + $reactor->getUsage();
    }

    #[Override]
    public function getReactorWrapper(): ?ReactorWrapperInterface
    {
        if ($this->reactorWrapper === null) {
            $this->reactorWrapper = $this->reactorWrapperFactory->createReactorWrapper($this);
        }

        return $this->reactorWrapper;
    }

    #[Override]
    public function getAlertState(): SpacecraftAlertStateEnum
    {
        return $this->getComputerSystemDataMandatory()->getAlertState();
    }

    #[Override]
    public function setAlertState(SpacecraftAlertStateEnum $alertState): ?string
    {
        $msg = $this->spacecraftStateChanger->changeAlertState($this, $alertState);
        $this->epsUsage = $this->reloadEpsUsage();

        return $msg;
    }

    #[Override]
    public function isUnalerted(): bool
    {
        return !$this->spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::COMPUTER)
            || $this->getComputerSystemDataMandatory()->isAlertGreen();
    }

    #[Override]
    public function getShieldRegenerationRate(): int
    {
        $regenerationPercentage = $this->get()->isSystemHealthy(SpacecraftSystemTypeEnum::SHIELDS) ? 10 : 0;

        $shield = $this->get()->getCondition()->getShield();
        $maxshield = $this->get()->getMaxShield();

        $result = (int) ceil(($maxshield / 100) * $regenerationPercentage);

        if ($result > $maxshield - $shield) {
            $result = $maxshield - $shield;
        }

        return $result;
    }

    /**
     * highest damage first, then prio
     *
     * @return SpacecraftSystem[]
     */
    #[Override]
    public function getDamagedSystems(): array
    {
        $damagedSystems = [];
        $prioArray = [];
        foreach ($this->spacecraft->getSystems() as $system) {
            if ($system->getStatus() < 100) {
                $damagedSystems[] = $system;
                $prioArray[$system->getSystemType()->value] = $system->getSystemType()->getPriority();
            }
        }

        // sort by damage and priority
        usort(
            $damagedSystems,
            function (SpacecraftSystem $a, SpacecraftSystem $b) use ($prioArray): int {
                if ($a->getStatus() === $b->getStatus()) {
                    return $prioArray[$b->getSystemType()->value] <=> $prioArray[$a->getSystemType()->value];
                }
                return ($a->getStatus() < $b->getStatus()) ? -1 : 1;
            }
        );

        return $damagedSystems;
    }

    #[Override]
    public function isSelectable(): bool
    {
        return $this->game->getUser()->getId() === $this->spacecraft->getUser()->getId()
            && $this->spacecraft->getType()->getModuleView() !== null;
    }

    #[Override]
    public function canBeRepaired(): bool
    {
        if (!$this->isUnalerted()) {
            return false;
        }

        if ($this->spacecraft->isShielded()) {
            return false;
        }

        if ($this->spacecraft->isCloaked()) {
            return false;
        }

        if ($this->getDamagedSystems() !== []) {
            return true;
        }

        return $this->spacecraft->getCondition()->getHull() < $this->spacecraft->getMaxHull();
    }

    #[Override]
    public function canFire(): bool
    {
        $ship = $this->spacecraft;
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
    public function canMan(): bool
    {
        $buildplan = $this->spacecraft->getBuildplan();

        return $buildplan !== null
            && $buildplan->getCrew() > 0
            && $this->spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT);
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

        $neededSpareParts = $neededParts[CommodityTypeConstants::COMMODITY_SPARE_PART];
        $neededSystemComponents = $neededParts[CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT];

        return [
            new ShipRepairCost($neededSpareParts, CommodityTypeConstants::COMMODITY_SPARE_PART, CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SPARE_PART)),
            new ShipRepairCost($neededSystemComponents, CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT, CommodityTypeConstants::getDescription(CommodityTypeConstants::COMMODITY_SYSTEM_COMPONENT))
        ];
    }

    #[Override]
    public function getPossibleTorpedoTypes(): array
    {
        if ($this->spacecraft->hasSpacecraftSystem(SpacecraftSystemTypeEnum::TORPEDO_STORAGE)) {
            return $this->torpedoTypeRepository->getAll();
        }

        return $this->torpedoTypeRepository->getByLevel($this->spacecraft->getRump()->getTorpedoLevel());
    }

    #[Override]
    public function getTractoredShipWrapper(): ?ShipWrapperInterface
    {
        $tractoredShip = $this->spacecraft->getTractoredShip();
        if ($tractoredShip === null) {
            return null;
        }

        return $this->spacecraftWrapperFactory->wrapShip($tractoredShip);
    }

    #[Override]
    public function getStateIconAndTitle(): ?array
    {
        return $this->stateIconAndTitle->getStateIconAndTitle($this);
    }

    #[Override]
    public function getTakeoverTicksLeft(?ShipTakeover $takeover = null): int
    {
        $takeover ??= $this->spacecraft->getTakeoverActive();
        if ($takeover === null) {
            throw new BadMethodCallException('should not call when active takeover is null');
        }

        $currentTurn = $this->game->getCurrentRound()->getTurn();

        return $takeover->getStartTurn() + ShipTakeoverManagerInterface::TURNS_TO_TAKEOVER - $currentTurn;
    }

    #[Override]
    public function getCrewStyle(): string
    {
        $ship = $this->spacecraft;
        $excessCrew = $ship->getExcessCrewCount();

        if ($excessCrew === 0) {
            return "";
        }

        return $excessCrew > 0 ? "color: green;" : "color: red;";
    }

    /**
     * @template T2
     * @param class-string<T2> $className
     *
     * @return T2|null
     */
    public function getSpecificShipSystem(SpacecraftSystemTypeEnum $systemType, string $className)
    {
        return $this->systemDataDeserializer->getSpecificShipSystem(
            $this->spacecraft,
            $systemType,
            $className,
            $this->shipSystemDataCache,
            $this->spacecraftWrapperFactory
        );
    }

    #[Override]
    public function __toString(): string
    {
        $systems = implode(",\n", $this->spacecraft->getSystems()
            ->filter(fn($system): bool => $system->getData() !== null)
            ->map(fn($system): string => $system->__toString())
            ->toArray());

        return sprintf(
            "spacecraft: {%s,\n  systems: [\n%s\n}\n]",
            $this->spacecraft->__toString(),
            $systems
        );
    }
}
