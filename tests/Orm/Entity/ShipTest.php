<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\StuTestCase;

class ShipTest extends StuTestCase
{
    private ShipInterface $subject;

    public function setUp(): void
    {
        $this->subject = new Ship();
    }

    public function testUpdateLocationWhenMap(): void
    {
        $map = $this->mock(MapInterface::class);

        $this->subject->updateLocation($map);

        $this->assertSame($map, $this->subject->getMap());
        $this->assertNull($this->subject->getStarsystemMap());
    }

    public function testUpdateLocationWhenSystemMapAndNotWormhole(): void
    {
        $map = $this->mock(MapInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);

        $systemMap->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->subject->updateLocation($map);
        $this->subject->updateLocation($systemMap);

        $this->assertSame($systemMap, $this->subject->getStarsystemMap());
        $this->assertSame($map, $this->subject->getMap());
    }

    public function testUpdateLocationWhenSystemMapAndWormhole(): void
    {
        $map = $this->mock(MapInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);

        $systemMap->shouldReceive('getSystem->isWormhole')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->subject->updateLocation($map);
        $this->subject->updateLocation($systemMap);

        $this->assertSame($systemMap, $this->subject->getStarsystemMap());
        $this->assertNull($this->subject->getMap());
    }
}
