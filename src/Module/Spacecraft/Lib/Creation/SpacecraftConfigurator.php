<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\LocationInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

/**
 * @template T of SpacecraftWrapperInterface
 * 
 * @implements SpacecraftConfiguratorInterface<T>
 */
class SpacecraftConfigurator implements SpacecraftConfiguratorInterface
{
    /**
     * @psalm-param T $wrapper
     */
    public function __construct(
        private SpacecraftWrapperInterface $wrapper,
        private TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private ShipTorpedoManagerInterface $torpedoManager,
        private CrewCreatorInterface $crewCreator,
        private CrewAssignmentRepositoryInterface $shipCrewRepository,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        private GameControllerInterface $game
    ) {}

    #[Override]
    public function setLocation(LocationInterface $location): SpacecraftConfiguratorInterface
    {
        $this->wrapper->get()->setLocation($location);

        return $this;
    }

    #[Override]
    public function loadEps(int $percentage): SpacecraftConfiguratorInterface
    {
        $epsSystem = $this->wrapper->getEpsSystemData();

        if ($epsSystem !== null) {
            $epsSystem
                ->setEps((int)floor($epsSystem->getTheoreticalMaxEps() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    #[Override]
    public function loadBattery(int $percentage): SpacecraftConfiguratorInterface
    {
        $epsSystem = $this->wrapper->getEpsSystemData();

        if ($epsSystem !== null) {
            $epsSystem
                ->setBattery((int)floor($epsSystem->getMaxBattery() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    #[Override]
    public function loadReactor(int $percentage): SpacecraftConfiguratorInterface
    {
        $reactor = $this->wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor->setLoad((int)floor($reactor->getCapacity() / 100 * $percentage));
        }

        return $this;
    }

    #[Override]
    public function loadWarpdrive(int $percentage): SpacecraftConfiguratorInterface
    {
        $warpdrive = $this->wrapper->getWarpDriveSystemData();
        if ($warpdrive !== null) {
            $warpdrive
                ->setWarpDrive((int)floor($warpdrive->getMaxWarpdrive() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    #[Override]
    public function maxOutSystems(): SpacecraftConfiguratorInterface
    {
        $this->loadEps(100)
            ->loadReactor(100)
            ->loadWarpdrive(100)
            ->loadBattery(100);

        $ship = $this->wrapper->get();

        $ship->setShield($ship->getMaxShield());

        return $this;
    }

    #[Override]
    public function createCrew(?int $amount = null): SpacecraftConfiguratorInterface
    {
        $ship = $this->wrapper->get();

        $buildplan = $ship->getBuildplan();
        if ($buildplan !== null) {
            if ($amount !== null && $amount >= 0) {
                $crewAmount = $amount;
            } else {
                $crewAmount = $buildplan->getCrew();
            }
            for ($j = 1; $j <= $crewAmount; $j++) {
                $crewAssignment = $this->crewCreator->create($ship->getUser()->getId());
                $crewAssignment->setSpacecraft($ship);
                $this->shipCrewRepository->save($crewAssignment);

                $ship->getCrewAssignments()->add($crewAssignment);
            }

            if ($crewAmount > 0) {
                $ship->getSpacecraftSystem(SpacecraftSystemTypeEnum::LIFE_SUPPORT)->setMode(SpacecraftSystemModeEnum::MODE_ALWAYS_ON);
            }
        }

        return $this;
    }

    #[Override]
    public function setAlertState(SpacecraftAlertStateEnum $alertState): SpacecraftConfiguratorInterface
    {
        $this->activatorDeactivatorHelper->setAlertState(
            $this->wrapper,
            $alertState,
            $this->game
        );

        return $this;
    }

    #[Override]
    public function setTorpedo(?int $torpedoTypeId = null): SpacecraftConfiguratorInterface
    {
        $ship = $this->wrapper->get();
        if ($ship->getMaxTorpedos() === 0) {
            return $this;
        }

        $ship = $this->wrapper->get();

        if ($torpedoTypeId !== null) {
            $torpedoType = $this->torpedoTypeRepository->find($torpedoTypeId);
            if ($torpedoType === null) {
                throw new RuntimeException(sprintf('torpedoTypeId %d does not exist', $torpedoTypeId));
            }
        } else {
            $torpedoLevel = $ship->getRump()->getTorpedoLevel();
            if ($torpedoLevel === 0) {
                return $this;
            }

            $torpedoTypes = $this->torpedoTypeRepository->getByLevel($torpedoLevel);
            if ($torpedoTypes === []) {
                return $this;
            }
            shuffle($torpedoTypes);

            $torpedoType = current($torpedoTypes);
        }

        $this->torpedoManager->changeTorpedo($this->wrapper, $ship->getMaxTorpedos(), $torpedoType);

        return $this;
    }

    #[Override]
    public function setSpacecraftName(string $name): SpacecraftConfiguratorInterface
    {
        $ship = $this->wrapper->get();
        $ship->setName($name);

        return $this;
    }

    #[Override]
    public function finishConfiguration()
    {
        $this->spacecraftRepository->save($this->wrapper->get());

        return $this->wrapper;
    }
}
