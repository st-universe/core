<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Component\Building\ColonyBuildingEffects;
use Stu\Component\Building\BuildingManagerInterface;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Lib\Transfer\Storage\StorageManagerInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\ColonySandbox;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class BuildingActionTest extends StuTestCase
{
    /**
     * @var MockInterface&StorageManagerInterface
     */
    private $storageManager;
    /**
     * @var MockInterface&BuildingManagerInterface
     */
    private $buildingManager;
    /**
     * @var MockInterface&ColonyLibFactoryInterface
     */
    private $colonyLibFactory;
    /**
     * @var MockInterface&PlanetFieldRepositoryInterface
     */
    private $planetFieldRepository;

    /**
     * @var MockInterface&PlanetField
     */
    private $field;

    private BuildingActionInterface $subject;


    #[\Override]
    public function setUp(): void
    {
        $this->storageManager = Mockery::mock(StorageManagerInterface::class);
        $this->buildingManager = Mockery::mock(BuildingManagerInterface::class);
        $this->colonyLibFactory = Mockery::mock(ColonyLibFactoryInterface::class);
        $this->planetFieldRepository = Mockery::mock(PlanetFieldRepositoryInterface::class);

        $this->field = $this->mock(PlanetField::class);

        $this->subject = new BuildingAction(
            $this->storageManager,
            $this->buildingManager,
            $this->planetFieldRepository,
            new ColonyBuildingEffects($this->planetFieldRepository),
            new BuildingCommodityDeltaTracker($this->colonyLibFactory)
        );
    }

    public function testActivateReturnsIfColonyLacksWorkers(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
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
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->once()
            ->andReturn($changeable);
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $building->shouldReceive('getWorkers')
            ->withNoArgs()
            ->twice()
            ->andReturn(8);
        $building->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BUILDING');

        $this->buildingManager->shouldReceive('activate')
            ->never();

        $game->shouldReceive('getInfo->addInformationf')
            ->once();

        $this->subject->activate($field, $game);
    }

    public function testActivateWithMultipleFieldsBlocksWhenProjectedCommodityTurnsNegative(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $field1 = $this->mock(PlanetField::class);
        $field2 = $this->mock(PlanetField::class);
        $building1 = $this->mock(Building::class);
        $building2 = $this->mock(Building::class);
        $commodity1 = $this->mock(BuildingCommodity::class);
        $commodity2 = $this->mock(BuildingCommodity::class);
        $colonyCommodityProduction = $this->mock(ColonyCommodityProductionInterface::class);
        $productionEntry = $this->mock(ColonyProduction::class);

        $host->shouldReceive('getId')
            ->withNoArgs()
            ->times(3)
            ->andReturn(7);
        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->twice()
            ->andReturn($changeable);
        $changeable->shouldReceive('getWorkless')
            ->withNoArgs()
            ->twice()
            ->andReturn(100);

        foreach ([$field1, $field2] as $field) {
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
            $field->shouldReceive('getHost')
                ->withNoArgs()
                ->once()
                ->andReturn($host);
        }

        $field1->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building1);
        $field1->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $field2->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building2);

        foreach ([$building1, $building2] as $building) {
            $building->shouldReceive('getWorkers')
                ->withNoArgs()
                ->once()
                ->andReturn(1);
        }

        $building1->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('B1');
        $building1->shouldReceive('getCommodities')
            ->withNoArgs()
            ->times(4)
            ->andReturn(new ArrayCollection([$commodity1]));

        $building2->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('B2');
        $building2->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$commodity2]));

        foreach ([$commodity1, $commodity2] as $commodity) {
            $commodity->shouldReceive('getCommodityId')
                ->withNoArgs()
                ->andReturn(
                    CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE,
                    CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE
                );
            $commodity->shouldReceive('getAmount')
                ->withNoArgs()
                ->andReturn(-1, -1);
        }

        $colonyCommodityProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->twice()
            ->andReturn([
                CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE => $productionEntry
            ]);
        $productionEntry->shouldReceive('getProduction')
            ->withNoArgs()
            ->twice()
            ->andReturn(1);

        $this->colonyLibFactory->shouldReceive('createColonyCommodityProduction')
            ->with($host)
            ->twice()
            ->andReturn($colonyCommodityProduction);

        $this->buildingManager->shouldReceive('activate')
            ->with($field1)
            ->once()
            ->andReturnTrue();
        $this->buildingManager->shouldReceive('activate')
            ->with($field2)
            ->never();

        $game->shouldReceive('getInfo->addInformationf')
            ->twice();

        $this->subject->activate($field1, $game);
        $this->subject->activate($field2, $game);
    }

    public function testDeactivateDeactivatesOrbitalMaintenanceConsumers(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $commodity = $this->mock(BuildingCommodity::class);
        $activeConsumerField = $this->mock(PlanetField::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('isActivateable')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->twice()
            ->andReturnTrue();
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $building->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BUILDING');
        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->twice()
            ->andReturn(new ArrayCollection([$commodity]));

        $commodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->andReturn(
                CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE,
                CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE
            );
        $commodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->andReturn(5, 5);

        $this->planetFieldRepository->shouldReceive('getCommodityConsumingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE, [1])
            ->once()
            ->andReturn([$activeConsumerField]);

        $activeConsumerField->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $activeConsumerField->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $activeConsumerField->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();

        $this->buildingManager->shouldReceive('deactivate')
            ->with($activeConsumerField)
            ->once();
        $this->buildingManager->shouldReceive('deactivate')
            ->with($field)
            ->once();

        $game->shouldReceive('getInfo->addInformationf')
            ->twice();

        $this->subject->deactivate($field, $game);
    }

    public function testRemoveHandlesOrbitalMaintenanceUpgradePathForColony(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $field = $this->mock(PlanetField::class);
        $activeConsumerField = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $host = $this->mock(Colony::class);
        $commodity = $this->mock(BuildingCommodity::class);

        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->times(3)
            ->andReturnTrue();
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(99);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(99)
            ->once()
            ->andReturnSelf();
        $field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(11);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$commodity]));
        $building->shouldReceive('getCosts')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());
        $building->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BUILDING');

        $commodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE);
        $commodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(3);

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();
        $this->planetFieldRepository->shouldReceive('getCommodityConsumingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE, [1])
            ->once()
            ->andReturn([$activeConsumerField]);

        $activeConsumerField->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $activeConsumerField->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $activeConsumerField->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $activeConsumerField->shouldReceive('setReactivateAfterUpgrade')
            ->with(99)
            ->once()
            ->andReturnSelf();
        $this->planetFieldRepository->shouldReceive('save')
            ->with($activeConsumerField)
            ->once();

        $this->buildingManager->shouldReceive('deactivate')
            ->with($activeConsumerField)
            ->once();
        $this->buildingManager->shouldReceive('remove')
            ->with($field, true)
            ->once();
        $this->storageManager->shouldReceive('upperStorage')
            ->never();

        $game->shouldReceive('getInfo->addInformationf')
            ->twice();
        $game->shouldReceive('getInfo->addInformation')
            ->once();

        $this->subject->remove($field, $game, true);
    }

    public function testRemoveExpectRemovalOfPreviousBuilding(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $building = $this->mock(Building::class);

        $this->field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $this->field->shouldReceive('getFieldId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $this->field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($this->mock(ColonySandbox::class));
        $this->field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $building->shouldReceive('isRemovable')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $building->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn('BUILDING');

        $this->buildingManager->shouldReceive('remove')
            ->with($this->field, false)
            ->once();

        $game->shouldReceive('getInfo->addInformationf')
            ->with(
                '%s auf Feld %d wurde demontiert',
                'BUILDING',
                42
            )
            ->once();

        $this->subject->remove($this->field, $game);
    }
}
