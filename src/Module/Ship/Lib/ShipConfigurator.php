<?php

namespace Stu\Module\Ship\Lib;

use RuntimeException;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemModeEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Map\Location;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Module\Ship\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\ShipCrewRepositoryInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\Orm\Repository\TorpedoTypeRepositoryInterface;

class ShipConfigurator implements ShipConfiguratorInterface
{
    private ShipWrapperInterface $wrapper;

    private TorpedoTypeRepositoryInterface $torpedoTypeRepository;

    private ShipTorpedoManagerInterface $torpedoManager;

    private CrewCreatorInterface $crewCreator;

    private ShipCrewRepositoryInterface $shipCrewRepository;

    private ShipRepositoryInterface $shipRepository;

    private ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper;

    private GameControllerInterface $game;

    public function __construct(
        ShipWrapperInterface $wrapper,
        TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        ShipTorpedoManagerInterface $torpedoManager,
        CrewCreatorInterface $crewCreator,
        ShipCrewRepositoryInterface $shipCrewRepository,
        ShipRepositoryInterface $shipRepository,
        ActivatorDeactivatorHelperInterface $activatorDeactivatorHelper,
        GameControllerInterface $game
    ) {
        $this->wrapper = $wrapper;
        $this->torpedoTypeRepository = $torpedoTypeRepository;
        $this->torpedoManager = $torpedoManager;
        $this->crewCreator = $crewCreator;
        $this->shipCrewRepository = $shipCrewRepository;
        $this->shipRepository = $shipRepository;
        $this->activatorDeactivatorHelper = $activatorDeactivatorHelper;
        $this->game = $game;
    }

    public function setLocation(MapInterface|StarSystemMapInterface|Location $location): ShipConfiguratorInterface
    {
        $this->wrapper->get()->setLocation($location);

        return $this;
    }

    public function loadEps(int $percentage): ShipConfiguratorInterface
    {
        $epsSystem = $this->wrapper->getEpsSystemData();

        if ($epsSystem !== null) {
            $epsSystem
                ->setEps((int)floor($epsSystem->getTheoreticalMaxEps() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    public function loadBattery(int $percentage): ShipConfiguratorInterface
    {
        $epsSystem = $this->wrapper->getEpsSystemData();

        if ($epsSystem !== null) {
            $epsSystem
                ->setBattery((int)floor($epsSystem->getMaxBattery() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    public function loadReactor(int $percentage): ShipConfiguratorInterface
    {
        $reactor = $this->wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor->setLoad((int)floor($reactor->getCapacity() / 100 * $percentage));
        }

        return $this;
    }

    public function loadWarpdrive(int $percentage): ShipConfiguratorInterface
    {
        $warpdrive = $this->wrapper->getWarpDriveSystemData();
        if ($warpdrive !== null) {
            $warpdrive
                ->setWarpDrive((int)floor($warpdrive->getMaxWarpdrive() / 100 * $percentage))
                ->update();
        }

        return $this;
    }

    public function maxOutSystems(): ShipConfiguratorInterface
    {
        $this->loadEps(100)
            ->loadReactor(100)
            ->loadWarpdrive(100)
            ->loadBattery(100);

        $ship = $this->wrapper->get();

        $ship->setShield($ship->getMaxShield());

        return $this;
    }

    public function createCrew(): ShipConfiguratorInterface
    {
        $ship = $this->wrapper->get();

        $buildplan = $ship->getBuildplan();
        if ($buildplan !== null) {
            $crewAmount = $buildplan->getCrew();
            for ($j = 1; $j <= $crewAmount; $j++) {
                $crewAssignment = $this->crewCreator->create($ship->getUser()->getId());
                $crewAssignment->setShip($ship);
                $this->shipCrewRepository->save($crewAssignment);

                $ship->getCrewAssignments()->add($crewAssignment);
            }

            if ($crewAmount > 0) {
                $ship->getShipSystem(ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT)->setMode(ShipSystemModeEnum::MODE_ALWAYS_ON);
            }
        }

        return $this;
    }

    public function setAlertState(ShipAlertStateEnum $alertState): ShipConfiguratorInterface
    {
        $this->activatorDeactivatorHelper->setAlertState(
            $this->wrapper,
            $alertState,
            $this->game
        );

        return $this;
    }

    public function setTorpedo(int $torpedoTypeId = null): ShipConfiguratorInterface
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

    public function setShipName(string $name): ShipConfiguratorInterface
    {
        $ship = $this->wrapper->get();
        $ship->setName($name);

        return $this;
    }

    public function finishConfiguration(): ShipWrapperInterface
    {
        $this->shipRepository->save($this->wrapper->get());

        return $this->wrapper;
    }
}
