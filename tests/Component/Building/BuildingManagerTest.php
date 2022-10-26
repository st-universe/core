<?php

declare(strict_types=1);

namespace Component\Building;

use Mockery\MockInterface;
use Stu\Component\Building\BuildingManager;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Entity\PlanetFieldInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingManagerTest extends StuTestCase
{
    /**
     * @var MockInterface|null|PlanetFieldRepositoryInterface
     */
    private ?MockInterface $planetFieldRepository;

    /**
     * @var MockInterface|null|ColonyRepositoryInterface
     */
    private ?MockInterface $colonyRepository;

    private ?BuildingManager $buildingManager;

    public function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);

        $this->buildingManager = new BuildingManager(
            $this->planetFieldRepository,
            $this->colonyRepository
        );
    }

    public function testActivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

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
        $building->shouldReceive('postActivation')
            ->with($colony)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->colonyRepository->shouldReceive('save')
            ->with($colony)
            ->once();

        $this->buildingManager->activate($field);
    }

    public function testDeactivateFailsIfNotActivateable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateFailsIfAlreadyInactive(): void
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

        $this->buildingManager->deactivate($field);
    }

    public function testDeactivateDeactivates(): void
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
            ->with($workless + $worker)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setWorkers')
            ->with($currentWorker - $worker)
            ->once()
            ->andReturnSelf();
        $colony->shouldReceive('setMaxBev')
            ->with($currentHousing - $housing)
            ->once();

        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->once()
            ->andReturn($worker);
        $building->shouldReceive('getHousing')
            ->withNoArgs()
            ->once()
            ->andReturn($housing);
        $building->shouldReceive('postDeactivation')
            ->with($colony)
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

        $field->shouldReceive('hasBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->buildingManager->remove($field);
    }

    public function testRemoveFailsIfBuildingIsNotRemoveable(): void
    {
        $field = $this->mock(PlanetFieldInterface::class);

        $field->shouldReceive('hasBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('getBuilding->isRemovable')
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

        $currentStorage = 555;
        $storage = 44;
        $currentEps = 33;
        $eps = 22;

        $field->shouldReceive('hasBuilding')
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
            ->once()
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

        $this->buildingManager->finish($field, true);
    }
}
