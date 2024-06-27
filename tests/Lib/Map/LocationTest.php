<?php

declare(strict_types=1);

namespace Stu\Lib\Map;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use InvalidArgumentException;
use Stu\Component\Anomaly\Type\AnomalyTypeEnum;
use Stu\Orm\Entity\AnomalyInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\StuTestCase;

class LocationTest extends StuTestCase
{
    public function testConstructorExpectExceptionWhenBothNull(): void
    {
        static::expectExceptionMessage('At least on of Map or systemMap has to be filled');
        static::expectException(InvalidArgumentException::class);

        new Location(null, null);
    }

    public function testConstructorExpectExceptionWhenMapNullButNotWormhole(): void
    {
        static::expectExceptionMessage('Map can only be null in Wormholes');
        static::expectException(InvalidArgumentException::class);

        $sysMap = $this->mock(StarSystemMapInterface::class);

        $sysMap->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        new Location(null, $sysMap);
    }

    public function testConstructorExpectExceptionWhenMapNotParentOfSystemMap(): void
    {
        static::expectExceptionMessage('System of SystemMap does not belong to current Map Field');
        static::expectException(InvalidArgumentException::class);

        $map = $this->mock(MapInterface::class);
        $otherMap = $this->mock(MapInterface::class);
        $sysMap = $this->mock(StarSystemMapInterface::class);

        $sysMap->shouldReceive('getSystem->getMapField')
            ->withNoArgs()
            ->once()
            ->andReturn($otherMap);

        new Location($map, $sysMap);
    }

    public function testGetShipsForMap(): void
    {
        $map = $this->mock(MapInterface::class);
        $ships = $this->mock(Collection::class);

        $map->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn($ships);

        $location = new Location($map, null);

        $this->assertSame($ships, $location->getShips());
    }

    public function testGetShipsForSystemMap(): void
    {
        $sysMap = $this->mock(StarSystemMapInterface::class);
        $ships = $this->mock(Collection::class);

        $sysMap->shouldReceive('getShips')
            ->withNoArgs()
            ->once()
            ->andReturn($ships);
        $sysMap->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $sysMap->shouldReceive('getSystem->getMapField')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $location = new Location(null, $sysMap);

        $this->assertSame($ships, $location->getShips());
    }

    public function testGetSectorString(): void
    {
        $map = $this->mock(MapInterface::class);

        $map->shouldReceive('getSectorString')
            ->withNoArgs()
            ->once()
            ->andReturn('SECTOR');

        $location = new Location($map, null);

        $this->assertSame('SECTOR', $location->getSectorString());
    }

    public function testGetAnomalies(): void
    {
        $map = $this->mock(MapInterface::class);
        $anomalies = $this->mock(Collection::class);
        $filteredAnomalies = $this->mock(Collection::class);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn($anomalies);
        $anomalies->shouldReceive('filter')
            ->once()
            ->andReturn($filteredAnomalies);

        $location = new Location($map, null);

        $this->assertSame($filteredAnomalies, $location->getAnomalies());
    }

    public function testHasAnomalyExpectFalseWhenNoAnomaliesPresent(): void
    {
        $map = $this->mock(MapInterface::class);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection());

        $location = new Location($map, null);

        $result = $location->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE);

        $this->assertFalse($result);
    }

    public function testHasAnomalyExpectFalseWhenNoActiveMatchingAnomalyPresent(): void
    {
        $map = $this->mock(MapInterface::class);
        $anomaly = $this->mock(AnomalyInterface::class);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$anomaly]));

        $anomaly->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $location = new Location($map, null);

        $result = $location->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE);

        $this->assertFalse($result);
    }

    public function testHasAnomalyExpectFalseWhenNoMatchingAnomalyPresent(): void
    {
        $map = $this->mock(MapInterface::class);
        $anomaly = $this->mock(AnomalyInterface::class);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$anomaly]));

        $anomaly->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $location = new Location($map, null);

        $result = $location->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE);

        $this->assertFalse($result);
    }

    public function testHasAnomalyExpectTrueWhenMatchingAnomalyPresentAndActive(): void
    {
        $map = $this->mock(MapInterface::class);
        $anomaly = $this->mock(AnomalyInterface::class);

        $map->shouldReceive('getAnomalies')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$anomaly]));

        $anomaly->shouldReceive('isActive')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $anomaly->shouldReceive('getAnomalyType->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(1);

        $location = new Location($map, null);

        $result = $location->hasAnomaly(AnomalyTypeEnum::SUBSPACE_ELLIPSE);

        $this->assertTrue($result);
    }
}
