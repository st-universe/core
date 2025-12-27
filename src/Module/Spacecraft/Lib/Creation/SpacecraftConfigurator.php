<?php

namespace Stu\Module\Spacecraft\Lib\Creation;

use InvalidArgumentException;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Module\Crew\Lib\CrewCreatorInterface;
use Stu\Component\Spacecraft\System\Control\AlertStateManagerInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Module\Spacecraft\Lib\Torpedo\ShipTorpedoManagerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Location;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
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
        private readonly SpacecraftWrapperInterface $wrapper,
        private readonly TorpedoTypeRepositoryInterface $torpedoTypeRepository,
        private readonly ShipTorpedoManagerInterface $torpedoManager,
        private readonly CrewCreatorInterface $crewCreator,
        private readonly CrewAssignmentRepositoryInterface $shipCrewRepository,
        private readonly AlertStateManagerInterface $alertStateManager,
        private readonly SpacecraftStartupInterface $spacecraftStartup
    ) {}

    #[\Override]
    public function setLocation(Location $location): SpacecraftConfiguratorInterface
    {
        $this->wrapper->get()->setLocation($location);

        return $this;
    }

    #[\Override]
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

    #[\Override]
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

    #[\Override]
    public function loadReactor(int $percentage): SpacecraftConfiguratorInterface
    {
        $reactor = $this->wrapper->getReactorWrapper();
        if ($reactor !== null) {
            $reactor->setLoad((int)floor($reactor->getCapacity() / 100 * $percentage));
        }

        return $this;
    }

    #[\Override]
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

    #[\Override]
    public function maxOutSystems(): SpacecraftConfiguratorInterface
    {
        $this->loadEps(100)
            ->loadReactor(100)
            ->loadWarpdrive(100)
            ->loadBattery(100);

        $ship = $this->wrapper->get();

        $ship->getCondition()->setShield($ship->getMaxShield());

        return $this;
    }

    #[\Override]
    public function createCrew(?int $amount = null): SpacecraftConfiguratorInterface
    {
        $spacecraft = $this->wrapper->get();

        $buildplan = $spacecraft->getBuildplan();
        if ($buildplan === null) {
            return $this;
        }

        $crewAmount = $amount !== null && $amount >= 0 ? $amount : $buildplan->getCrew();

        for ($j = 1; $j <= $crewAmount; $j++) {
            $crewAssignment = $this->crewCreator->create($spacecraft->getUser()->getId());
            $crewAssignment->setSpacecraft($spacecraft);
            $this->shipCrewRepository->save($crewAssignment);

            $spacecraft->getCrewAssignments()->add($crewAssignment);
        }

        $this->spacecraftStartup->startup($this->wrapper);

        return $this;
    }

    #[\Override]
    public function transferCrew(EntityWithCrewAssignmentsInterface $provider): SpacecraftConfiguratorInterface
    {
        $ship = $this->wrapper->get();

        $buildplan = $ship->getBuildplan();
        if ($buildplan === null) {
            return $this;
        }

        $this->crewCreator->createCrewAssignments($ship, $provider, $buildplan->getCrew());

        $this->spacecraftStartup->startup($this->wrapper);

        return $this;
    }

    #[\Override]
    public function setAlertState(SpacecraftAlertStateEnum $alertState): SpacecraftConfiguratorInterface
    {
        $this->alertStateManager->setAlertState(
            $this->wrapper,
            $alertState
        );

        return $this;
    }

    #[\Override]
    public function setTorpedo(?int $torpedoTypeId = null): SpacecraftConfiguratorInterface
    {
        $spacecraft = $this->wrapper->get();
        if ($spacecraft->getMaxTorpedos() === 0) {
            return $this;
        }

        if ($torpedoTypeId !== null) {
            $torpedoType = $this->torpedoTypeRepository->find($torpedoTypeId);
            if ($torpedoType === null) {
                throw new InvalidArgumentException(sprintf('torpedoTypeId %d does not exist', $torpedoTypeId));
            }
        } else {
            $torpedoTypes = $this->torpedoTypeRepository->getByLevel($spacecraft->getRump()->getTorpedoLevel());
            if ($torpedoTypes === []) {
                return $this;
            }

            shuffle($torpedoTypes);
            $torpedoType = current($torpedoTypes);
        }

        $this->torpedoManager->changeTorpedo($this->wrapper, $spacecraft->getMaxTorpedos(), $torpedoType);

        return $this;
    }

    #[\Override]
    public function setSpacecraftName(string $name): SpacecraftConfiguratorInterface
    {
        $this->wrapper->get()->setName($name);

        return $this;
    }

    #[\Override]
    public function finishConfiguration(): SpacecraftWrapperInterface
    {
        return $this->wrapper;
    }
}
