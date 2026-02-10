<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Lib\ColonyProduction\ColonyProduction;
use Stu\Component\Colony\Commodity\ColonyCommodityProductionInterface;
use Stu\Module\Commodity\CommodityTypeConstants;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\BuildingCommodity;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\PlanetField;
use Stu\StuTestCase;

class BuildingCommodityDeltaTrackerTest extends StuTestCase
{
    private MockInterface&ColonyLibFactoryInterface $colonyLibFactory;

    private BuildingCommodityDeltaTracker $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->colonyLibFactory = $this->mock(ColonyLibFactoryInterface::class);
        $this->subject = new BuildingCommodityDeltaTracker($this->colonyLibFactory);
    }

    public function testGetProductionWithDeltaIncludesRegisteredBuildingDelta(): void
    {
        $host = $this->mock(Colony::class);
        $building = $this->mock(Building::class);
        $buildingCommodity = $this->mock(BuildingCommodity::class);
        $colonyCommodityProduction = $this->mock(ColonyCommodityProductionInterface::class);
        $productionEntry = $this->mock(ColonyProduction::class);
        $commodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;

        $host->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(7);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildingCommodity]));
        $buildingCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);
        $buildingCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(-2);

        $this->subject->registerForBuilding($host, $building, 1);

        $this->colonyLibFactory->shouldReceive('createColonyCommodityProduction')
            ->with($host)
            ->once()
            ->andReturn($colonyCommodityProduction);
        $colonyCommodityProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn([$commodityId => $productionEntry]);
        $productionEntry->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn(5);

        $this->assertSame(3, $this->subject->getProductionWithDelta($host, $commodityId));
    }

    public function testGetBuildingCommodityAmountReturnsZeroIfCommodityIsMissing(): void
    {
        $building = $this->mock(Building::class);
        $commodity = $this->mock(BuildingCommodity::class);
        $commodityId = CommodityTypeConstants::COMMODITY_EFFECT_SHIPYARD_LOGISTICS;

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$commodity]));
        $commodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn(CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE);

        $this->assertSame(0, $this->subject->getBuildingCommodityAmount($building, $commodityId));
    }

    public function testRegisterOnSuccessfulDeactivationRegistersDelta(): void
    {
        $host = $this->mock(Colony::class);
        $field = $this->mock(PlanetField::class);
        $building = $this->mock(Building::class);
        $buildingCommodity = $this->mock(BuildingCommodity::class);
        $colonyCommodityProduction = $this->mock(ColonyCommodityProductionInterface::class);
        $commodityId = CommodityTypeConstants::COMMODITY_EFFECT_ORBITAL_MAINTENANCE;

        $field->shouldReceive('getHost')
            ->withNoArgs()
            ->once()
            ->andReturn($host);
        $field->shouldReceive('getBuilding')
            ->withNoArgs()
            ->once()
            ->andReturn($building);
        $field->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $host->shouldReceive('getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(7);

        $building->shouldReceive('getCommodities')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$buildingCommodity]));
        $buildingCommodity->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn($commodityId);
        $buildingCommodity->shouldReceive('getAmount')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $this->subject->registerOnSuccessfulDeactivation($field);

        $this->colonyLibFactory->shouldReceive('createColonyCommodityProduction')
            ->with($host)
            ->once()
            ->andReturn($colonyCommodityProduction);
        $colonyCommodityProduction->shouldReceive('getProduction')
            ->withNoArgs()
            ->once()
            ->andReturn([]);

        $this->assertSame(-2, $this->subject->getProductionWithDelta($host, $commodityId));
    }
}
