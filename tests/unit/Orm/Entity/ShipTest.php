<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\StuTestCase;

class ShipTest extends StuTestCase
{
    private Ship $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->subject = new Ship($this->mock(SpacecraftCondition::class));
    }

    public function testsetLocationWhenMap(): void
    {
        $map = $this->mock(Map::class);

        $this->subject->setLocation($map);

        $this->assertSame($map, $this->subject->getMap());
        $this->assertNull($this->subject->getStarsystemMap());
    }

    public function testsetLocationWhenSystemMapAndNotWormhole(): void
    {
        $map = $this->mock(Map::class);
        $systemMap = $this->mock(StarSystemMap::class);

        $systemMap->shouldReceive('getSystem->getMap')
            ->withNoArgs()
            ->once()
            ->andReturn($map);

        $this->subject->setLocation($systemMap);

        $this->assertSame($systemMap, $this->subject->getStarsystemMap());
        $this->assertSame($map, $this->subject->getMap());
    }

    public function testsetLocationWhenSystemMapAndWormhole(): void
    {
        $map = $this->mock(Map::class);
        $systemMap = $this->mock(StarSystemMap::class);

        $systemMap->shouldReceive('getSystem->getMap')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->setLocation($map);
        $this->subject->setLocation($systemMap);

        $this->assertSame($systemMap, $this->subject->getStarsystemMap());
        $this->assertNull($this->subject->getMap());
    }

    public function testSetDockedToExpectUndockingIfDocked(): void
    {
        $station = $this->mock(Station::class);
        $dockedShips = new ArrayCollection();
        $station->shouldReceive('getDockedShips')
            ->withNoArgs()
            ->andReturn($dockedShips);

        // ADDING
        $this->subject->setDockedTo($station);
        $this->assertSame($station, $this->subject->getDockedTo());
        $this->assertTrue($dockedShips->contains($this->subject));

        // REMOVAL
        $this->subject->setDockedTo(null);
        $this->assertNull($this->subject->getDockedTo());
        $this->assertTrue($dockedShips->isEmpty());
    }
}
