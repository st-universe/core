<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Lib\Map\Location;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\StuTestCase;

class PanelBoundariesTest extends StuTestCase
{
    public function testFromArray(): void
    {
        $layer = mock(LayerInterface::class);

        $result = PanelBoundaries::fromArray(
            [
                'minx' => 1,
                'maxx' => 5,
                'miny' => 42,
                'maxy' => 99
            ],
            $layer
        );

        $this->assertTrue($result->isOnMap());
        $this->assertEquals(1, $result->getMinX());
        $this->assertEquals(5, $result->getMaxX());
        $this->assertEquals(42, $result->getMinY());
        $this->assertEquals(99, $result->getMaxY());
    }

    public function testFromSystem(): void
    {
        $system = mock(StarSystemInterface::class);

        $system->shouldReceive('getMaxX')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $system->shouldReceive('getMaxY')
            ->withNoArgs()
            ->once()
            ->andReturn(99);

        $result = PanelBoundaries::fromSystem(
            $system
        );

        $this->assertFalse($result->isOnMap());
        $this->assertEquals(1, $result->getMinX());
        $this->assertEquals(42, $result->getMaxX());
        $this->assertEquals(1, $result->getMinY());
        $this->assertEquals(99, $result->getMaxY());
    }

    public function testFromMapLocation(): void
    {
        $location = mock(Location::class);
        $map = mock(MapInterface::class);
        $layer = mock(LayerInterface::class);

        $location->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $map->shouldReceive('getLayer')
            ->withNoArgs()
            ->andReturn($layer);
        $map->shouldReceive('getX')
            ->withNoArgs()
            ->andReturn(8);
        $map->shouldReceive('getY')
            ->withNoArgs()
            ->andReturn(14);

        $layer->shouldReceive('getWidth')
            ->withNoArgs()
            ->once()
            ->andReturn(10);
        $layer->shouldReceive('getHeight')
            ->withNoArgs()
            ->once()
            ->andReturn(15);

        $result = PanelBoundaries::fromLocation(
            $location,
            5
        );

        $this->assertTrue($result->isOnMap());
        $this->assertEquals(3, $result->getMinX());
        $this->assertEquals(10, $result->getMaxX());
        $this->assertEquals(9, $result->getMinY());
        $this->assertEquals(15, $result->getMaxY());
    }

    public function testFromSystemMapLocation(): void
    {
        $location = mock(Location::class);
        $systemMap = mock(StarSystemMapInterface::class);
        $system = mock(StarSystemInterface::class);

        $location->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($systemMap);

        $systemMap->shouldReceive('getSystem')
            ->withNoArgs()
            ->andReturn($system);
        $systemMap->shouldReceive('getX')
            ->withNoArgs()
            ->andReturn(2);
        $systemMap->shouldReceive('getY')
            ->withNoArgs()
            ->andReturn(3);

        $system->shouldReceive('getMaxX')
            ->withNoArgs()
            ->once()
            ->andReturn(10);
        $system->shouldReceive('getMaxY')
            ->withNoArgs()
            ->once()
            ->andReturn(15);

        $result = PanelBoundaries::fromLocation(
            $location,
            5
        );

        $this->assertFalse($result->isOnMap());
        $this->assertEquals(1, $result->getMinX());
        $this->assertEquals(7, $result->getMaxX());
        $this->assertEquals(1, $result->getMinY());
        $this->assertEquals(8, $result->getMaxY());

        $this->assertEquals([1, 2, 3, 4, 5, 6, 7], $result->getColumnRange());
        $this->assertEquals([1, 2, 3, 4, 5, 6, 7, 8], $result->getRowRange());
    }
}
