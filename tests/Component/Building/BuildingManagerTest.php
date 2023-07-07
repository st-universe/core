<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingFunctionInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingManagerTest extends StuTestCase
{
    /** @var MockInterface&PlanetFieldRepositoryInterface */
    private MockInterface $planetFieldRepository;

    /** @var MockInterface&ColonyRepositoryInterface */
    private MockInterface $colonyRepository;

    /** @var MockInterface&BuildingPostActionInterface */
    private MockInterface $buildingPostAction;

    /** @var MockInterface&BuildingFunctionActionMapperInterface  */
    private MockInterface $buildingFunctionActionMapper;

    private BuildingManager $buildingManager;

    public function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->buildingPostAction = $this->mock(BuildingPostActionInterface::class);
        $this->buildingFunctionActionMapper = $this->mock(BuildingFunctionActionMapperInterface::class);

        $this->buildingManager = new BuildingManager(
            $this->planetFieldRepository,
            $this->colonyRepository,
            $this->buildingFunctionActionMapper,
            $this->buildingPostAction
        );
    }

    public function testActivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(BuildingInterface::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfAlreadyActive(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

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
            ->andReturn($this->mock(BuildingInterface::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfDamaged(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

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
            ->andReturn($this->mock(BuildingInterface::class));

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->activate($field);
    }

    public function testActivateFailsOnLackOfWorklessPeople(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

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
        $field->shouldReceive('getColony->getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn(555);

        $this->buildingManager->activate($field);
    }

    public function testActivateActivates(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $building = $this->mock(BuildingInterface::class);

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
        $field->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $field->shouldReceive('setActive')
            ->with(1)
            ->once();

        $colony->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($workless);
        $colony->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($currentHousing);
        $colony->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);
        $colony->shouldReceive('setWorkless')
            ->with($workless - $worker)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setWorkers')
            ->with($currentWorker + $worker)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxBev')
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
            ->with($building, $colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->activate($field);
    }

    public function testDeactivateFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(BuildingInterface::class));
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateFailsIfAlreadyInactive(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(BuildingInterface::class));
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
        $field = $this->mock(PlanetFieldInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $building = $this->mock(BuildingInterface::class);

        $currentWorker = 33;
        $currentWorkless = 55;
        $currentHousing = 88;

        $worker = 6;
        $housing = 0;

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
        $field->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $colony->shouldReceive('getMaxBev')
            ->withNoArgs()
            ->once()
            ->andReturn($currentHousing);
        $colony->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);

        $newWorkless = $currentWorkless + $worker;
        $colony->shouldReceive('setWorkless')
            ->with($newWorkless)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorkless);

        $newWorkers = $currentWorker - $worker;
        $colony->shouldReceive('setWorkers')
            ->with($newWorkers)
            ->once()
            ->andReturnSelf();

        $newHousing = $currentHousing - $housing;
        $colony->shouldReceive('setMaxBev')
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
            ->with($building, $colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateDeactivatesForHousing(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $building = $this->mock(BuildingInterface::class);

        $currentWorker = 33;
        $currentWorkless = 55;
        $currentHousing = 88;

        $worker = 0;
        $housing = 11;

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
        $field->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once();

        $colony->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorkless);
        $colony->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($currentWorker);

        $newWorkers = $currentWorker - $worker;
        $colony->shouldReceive('setWorkers')
            ->with($newWorkers)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setWorkless')
            ->with($currentWorkless)
            ->once()
            ->andReturnSelf();

        $newHousing = $currentHousing - $housing;
        $colony->shouldReceive('setMaxBev')
            ->with($newHousing)
            ->once();
        $colony->shouldReceive('getMaxBev')
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
            ->with($building, $colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->deactivate($field);
    }

    public function testRemoveFailsOnMissingBuilding(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->remove($field);
    }

    public function testRemoveFailsIfBuildingIsNotRemoveable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $building = $this->mock(BuildingInterface::class);

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

    public function testRemoveRemoves(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);
        $building = $this->mock(BuildingInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $function = $this->mock(BuildingFunctionInterface::class);
        $buildingAction = $this->mock(BuildingActionHandlerInterface::class);

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 33;
        $eps = 22;
        $functionId = 123;

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->twice()
            ->andReturn($building);
        $field->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
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

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($functionId);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($functionId)
            ->once()
            ->andReturn($buildingAction);

        $buildingAction->shouldReceive('destruct')
            ->with($functionId, $colony)
            ->once();

        $colony->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $colony->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $colony->shouldReceive('setMaxStorage')
            ->with($currentStorage - $storage)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxEps')
            ->with($currentEps - $eps)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->remove($field);
    }

    public function testFinishFailsIfNoBuildingAvailable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->finish($field);
    }

    public function testFinishFinishesAndActivates(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);
        $building = $this->mock(BuildingInterface::class);
        $colony = $this->mock(ColonyInterface::class);

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 444;
        $eps = 33;
        $integrity = 777;

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->twice()
            ->andReturn($building);
        $field->shouldReceive('getColony')
            ->withNoArgs()
            ->once()
            ->andReturn($colony);
        $field->shouldReceive('setActive')
            ->with(0)
            ->once()
            ->andReturnSelf();
        $field->shouldReceive('setIntegrity')
            ->with($integrity)
            ->once();
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

        $colony->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn($currentStorage);
        $colony->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn($currentEps);
        $colony->shouldReceive('setMaxStorage')
            ->with($currentStorage + $storage)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxEps')
            ->with($currentEps + $eps)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->finish($field);
    }
}
