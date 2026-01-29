<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\ColonySandboxRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingManagerTest extends StuTestCase
{
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;
    private MockInterface&ColonyRepositoryInterface $colonyRepository;
    private MockInterface&ColonySandboxRepositoryInterface $colonySandboxRepository;
    private MockInterface&BuildingPostActionInterface $buildingPostAction;
    private MockInterface&BuildingFunctionActionMapperInterface  $buildingFunctionActionMapper;

    private BuildingManager $buildingManager;

    #[\Override]
    public function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->colonySandboxRepository = $this->mock(ColonySandboxRepositoryInterface::class);
        $this->buildingPostAction = $this->mock(BuildingPostActionInterface::class);
        $this->buildingFunctionActionMapper = $this->mock(BuildingFunctionActionMapperInterface::class);

        $this->buildingManager = new BuildingManager(
            $this->planetFieldRepository,
            $this->colonyRepository,
            $this->colonySandboxRepository,
            $this->buildingFunctionActionMapper,
            $this->buildingPostAction
        );
    }

    public function testActivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Building::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfAlreadyActive(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Building::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfDamaged(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('hasHighDamage')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Building::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsOnLackOfWorklessPeople(): void
    {
        $field = $this->mock(PlanetField::class);
        $colony = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('hasHighDamage')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('getBuilding->getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn(666);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($colony);

        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn(555);
        $colony->shouldReceive('getChangeable')
            ->withNoArgs()
            ->once()
            ->andReturn($changeable);

        $this->buildingManager->activate($field);
    }

    public function testActivateActivates(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $worker = 6;
        $currentWorker = 33;
        $currentHousing = 88;
        $workless = 55;
        $housing = 11;

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('hasHighDamage')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('setActive')
            ->with(1)
            ->once();

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->once()
            ->andReturn($changeable);
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($workless);
        $changeable->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($currentHousing);
        $changeable->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);
        $changeable->shouldReceive('setWorkless')
            ->with($workless - $worker)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setWorkers')
            ->with($currentWorker + $worker)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxBev')
            ->with($currentHousing + $housing)
            ->once();

        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($worker);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn($housing);

        $this->buildingPostAction->shouldReceive('handleActivation')
            ->with($building, $host)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->once();

        $this->buildingManager->activate($field);
    }

    public function testDeactivateFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Building::class));
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateFailsIfAlreadyInactive(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(Building::class));
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateDeactivatesForProduction(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $currentWorker = 33;
        $currentWorkless = 55;
        $currentHousing = 88;

        $worker = 6;
        $housing = 0;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $changeable->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($currentHousing);
        $changeable->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);

        $newWorkless = $currentWorkless + $worker;
        $changeable->shouldReceive('setWorkless')
            ->with($newWorkless)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorkless);

        $newWorkers = $currentWorker - $worker;
        $changeable->shouldReceive('setWorkers')
            ->with($newWorkers)
            ->once()
            ->andReturnSelf();

        $newHousing = $currentHousing;
        $changeable->shouldReceive('setMaxBev')
            ->with($newHousing)
            ->once();

        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($worker);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn($housing);

        $this->buildingPostAction->shouldReceive('handleDeactivation')
            ->with($building, $host)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->once();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateDeactivatesForHousing(): void
    {
        $field = $this->mock(PlanetField::class);
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $currentWorker = 33;
        $currentWorkless = 55;
        $currentHousing = 88;

        $worker = 0;
        $housing = 11;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorkless);
        $changeable->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);

        $newWorkers = $currentWorker;
        $changeable->shouldReceive('setWorkers')
            ->with($newWorkers)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setWorkless')
            ->with($currentWorkless)
            ->once()
            ->andReturnSelf();

        $newHousing = $currentHousing - $housing;
        $changeable->shouldReceive('setMaxBev')
            ->with($newHousing)
            ->once();
        $changeable->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($currentHousing, $newHousing, $newHousing);

        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($worker);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn($housing);

        $this->buildingPostAction->shouldReceive('handleDeactivation')
            ->with($building, $host)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->once();

        $this->buildingManager->deactivate($field);
    }

    public function testRemoveFailsOnMissingBuilding(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->remove($field);
    }

    public function testRemoveFailsIfBuildingIsNotRemoveable(): void
    {
        $field = $this->mock(PlanetField::class);

        $building = $this->mock(Building::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);

        $building->shouldReceive('isRemovable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->remove($field);
    }

    public function testRemoveRemovesWhenNotActivateable(): void
    {
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $function = $this->mock(BuildingFunction::class);
        $buildingAction = $this->mock(BuildingActionHandlerInterface::class);

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 33;
        $eps = 22;
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->twice()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('clearBuilding')
            ->withNoArgs()
            ->once();
        $field->shouldReceive('isUnderConstruction')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $building->shouldReceive('isRemovable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $building->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $building->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));
        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingFunction);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($buildingFunction)
            ->once()
            ->andReturn($buildingAction);

        $buildingAction->shouldReceive('destruct')
            ->with($buildingFunction, $host)
            ->once();

        $changeable->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $changeable->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $changeable->shouldReceive('setMaxStorage')
            ->with($currentStorage - $storage)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with($currentEps - $eps)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->once();

        $this->buildingManager->remove($field);
    }

    public function testRemoveRemovesExpectDeactivationWhenActive(): void
    {
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $function = $this->mock(BuildingFunction::class);
        $buildingAction = $this->mock(BuildingActionHandlerInterface::class);
        $currentStorage = 555;
        $storage = 44;
        $currentEps = 33;
        $eps = 22;
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;
        $buildingWorkers = 123;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('clearBuilding')
            ->withNoArgs()
            ->once();
        $field->shouldReceive('isUnderConstruction')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->twice()
            ->andReturnTrue();
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $building->shouldReceive('isRemovable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $building->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $building->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingWorkers);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));
        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->times(3)
            ->andReturn(new ArrayCollection());

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingFunction);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($buildingFunction)
            ->once()
            ->andReturn($buildingAction);

        $buildingAction->shouldReceive('destruct')
            ->with($buildingFunction, $host)
            ->once();

        $changeable->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $changeable->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $changeable->shouldReceive('setMaxStorage')
            ->with($currentStorage - $storage)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with($currentEps - $eps)
            ->once();
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $changeable->shouldReceive('setWorkless')
            ->with($buildingWorkers)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingWorkers);
        $changeable->shouldReceive('setWorkers')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn(200);
        $changeable->shouldReceive('setMaxBev')
            ->with(100)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->twice();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->twice();
        $this->buildingPostAction->shouldReceive('handleDeactivation')
            ->with($building, $host)
            ->once();

        $this->buildingManager->remove($field);
    }

    public function testRemoveExpectRemovalWhenUpgrade(): void
    {
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $function = $this->mock(BuildingFunction::class);
        $buildingAction = $this->mock(BuildingActionHandlerInterface::class);

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 33;
        $eps = 22;
        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;
        $buildingWorkers = 123;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('clearBuilding')
            ->withNoArgs()
            ->once();
        $field->shouldReceive('isUnderConstruction')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->twice()
            ->andReturnTrue();
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $building->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $building->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingWorkers);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));
        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection());

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingFunction);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($buildingFunction)
            ->once()
            ->andReturn($buildingAction);

        $buildingAction->shouldReceive('destruct')
            ->with($buildingFunction, $host)
            ->once();

        $changeable->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $changeable->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $changeable->shouldReceive('setMaxStorage')
            ->with($currentStorage - $storage)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with($currentEps - $eps)
            ->once();
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $changeable->shouldReceive('setWorkless')
            ->with($buildingWorkers)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingWorkers);
        $changeable->shouldReceive('setWorkers')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn(200);
        $changeable->shouldReceive('setMaxBev')
            ->with(100)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->twice();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->twice();
        $this->buildingPostAction->shouldReceive('handleDeactivation')
            ->with($building, $host)
            ->once();

        $this->buildingManager->remove($field, true);
    }

    public function testFinishFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->finish($field);
    }

    public function testFinishFinishesAndActivates(): void
    {
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 444;
        $eps = 33;
        $integrity = 777;
        $fieldId = 42;

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->andReturn($changeable);
        $host->shouldReceive('getPlanetFields')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->twice()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->andReturn($host);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $field->shouldReceive('setIntegrity')
            ->with($integrity)
            ->once();
        $field->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn($fieldId);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn($fieldId);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once()
            ->andReturnSelf();
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $building->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $building->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($storage);
        $building->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($eps);
        $building->shouldReceive('getIntegrity')
            ->withNoArgs()
            ->once()
            ->andReturn($integrity);
        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $changeable->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $changeable->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $changeable->shouldReceive('setMaxStorage')
            ->with($currentStorage + $storage)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with($currentEps + $eps)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->twice();
        $this->colonyRepository->shouldReceive('save')
            ->with($host)
            ->once();

        $this->buildingManager->finish($field);
    }
}
