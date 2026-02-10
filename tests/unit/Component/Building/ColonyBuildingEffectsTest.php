<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\ColonyChangeable;
use Stu\Orm\Entity\PlanetField;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class ColonyBuildingEffectsTest extends StuTestCase
{
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;

    private ColonyBuildingEffects $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->planetFieldRepository = $this->mock(PlanetFieldRepositoryInterface::class);

        $this->subject = new ColonyBuildingEffects($this->planetFieldRepository);
    }

    public function testHasEnoughUndergroundLogisticsReturnsTrueForBalancedProduction(): void
    {
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $buildingCommodity = $this->mock(BuildingCommodity::class);
        $producerField = $this->mock(PlanetField::class);
        $producerBuilding = $this->mock(Building::class);
        $producerCommodity = $this->mock(BuildingCommodity::class);
        $consumerField = $this->mock(PlanetField::class);
        $consumerBuilding = $this->mock(Building::class);
        $consumerCommodity = $this->mock(BuildingCommodity::class);

        $producerField->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $producerField->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($producerBuilding);

        $producerBuilding->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$producerCommodity]));
        $producerCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS);
        $producerCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $consumerField->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($consumerBuilding);

        $consumerBuilding->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$consumerCommodity]));
        $consumerCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS);
        $consumerCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(-3);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildingCommodity]));
        $buildingCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS);
        $buildingCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(-2);

        $this->planetFieldRepository->shouldReceive('getCommodityProducingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS)
            ->once()
            ->andReturn([$producerField]);
        $this->planetFieldRepository->shouldReceive('getCommodityConsumingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS, [1])
            ->once()
            ->andReturn([$consumerField]);

        $this->assertTrue($this->subject->hasEnoughUndergroundLogistics($host, $building));
    }

    public function testAdjustUndergroundLogisticsCapacityAdjustsStorageAndEps(): void
    {
        $host = $this->mock(Colony::class);
        $changeable = $this->mock(ColonyChangeable::class);
        $building = $this->mock(Building::class);
        $buildingCommodity = $this->mock(BuildingCommodity::class);
        $fieldA = $this->mock(PlanetField::class);
        $fieldB = $this->mock(PlanetField::class);
        $buildingA = $this->mock(Building::class);
        $buildingB = $this->mock(Building::class);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildingCommodity]));
        $buildingCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS);
        $buildingCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $fieldA->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingA);
        $fieldB->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingB);

        $buildingA->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(10);
        $buildingA->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(3);
        $buildingB->shouldReceive('getStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $buildingB->shouldReceive('getEpsStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $host->shouldReceive('getChangeable')
            ->withNoArgs()
            ->once()
            ->andReturn($changeable);

        $changeable->shouldReceive('getMaxStorage')
            ->withNoArgs()
            ->once()
            ->andReturn(100);
        $changeable->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->once()
            ->andReturn(50);
        $changeable->shouldReceive('setMaxStorage')
            ->with(115)
            ->once()
            ->andReturnSelf();
        $changeable->shouldReceive('setMaxEps')
            ->with(55)
            ->once();

        $this->planetFieldRepository->shouldReceive('getCommodityConsumingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_UNDERGROUND_LOGISTICS, [0, 1])
            ->once()
            ->andReturn([$fieldA, $fieldB]);

        $this->subject->adjustUndergroundLogisticsCapacity($building, $host, 1);
    }

    public function testDeactivateOrbitalMaintenanceConsumersDeactivatesAndMarks(): void
    {
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $buildingCommodity = $this->mock(BuildingCommodity::class);
        $activeField = $this->mock(PlanetField::class);
        $inactiveField = $this->mock(PlanetField::class);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildingCommodity]));
        $buildingCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE);
        $buildingCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $activeField->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();
        $activeField->shouldReceive('setReactivateAfterUpgrade')
            ->with(77)
            ->once();

        $inactiveField->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $this->planetFieldRepository->shouldReceive('getCommodityConsumingByHostAndCommodity')
            ->with($host, CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE, [1])
            ->once()
            ->andReturn([$activeField, $inactiveField]);
        $this->planetFieldRepository->shouldReceive('save')
            ->with($activeField)
            ->once();

        $deactivateCount = 0;
        $afterDeactivateCount = 0;

        $result = $this->subject->deactivateOrbitalMaintenanceConsumers(
            $building,
            $host,
            function (PlanetField $field) use ($activeField, &$deactivateCount): void {
                $this->assertSame($activeField, $field);
                $deactivateCount++;
            },
            function (PlanetField $field) use ($activeField, &$afterDeactivateCount): void {
                $this->assertSame($activeField, $field);
                $afterDeactivateCount++;
            },
            77
        );

        $this->assertSame(1, $result);
        $this->assertSame(1, $deactivateCount);
        $this->assertSame(1, $afterDeactivateCount);
    }

    public function testClearReactivationMarkersClearsSingleMarker(): void
    {
        $host = $this->mock(Colony::class);
        $field = $this->mock(PlanetField::class);

        $field->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(5);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(7);
        $field->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($field)
            ->once();

        $this->subject->clearReactivationMarkers($field, $host);
    }

    public function testClearReactivationMarkersClearsAllMarkersById(): void
    {
        $host = $this->mock(Colony::class);
        $field = $this->mock(PlanetField::class);
        $fieldA = $this->mock(PlanetField::class);
        $fieldB = $this->mock(PlanetField::class);
        $fieldC = $this->mock(PlanetField::class);

        $field->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(9);
        $field->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(9);

        $host->shouldReceive('getPlanetFields')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$fieldA, $fieldB, $fieldC]));

        $fieldA->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(9);
        $fieldA->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $fieldB->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(4);

        $fieldC->shouldReceive('getReactivateAfterUpgrade')
            ->withNoArgs()
            ->once()
            ->andReturn(9);
        $fieldC->shouldReceive('setReactivateAfterUpgrade')
            ->with(null)
            ->once();

        $this->planetFieldRepository->shouldReceive('save')
            ->with($fieldA)
            ->once();
        $this->planetFieldRepository->shouldReceive('save')
            ->with($fieldC)
            ->once();

        $this->subject->clearReactivationMarkers($field, $host);
    }
}

