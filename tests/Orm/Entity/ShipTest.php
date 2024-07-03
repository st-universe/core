<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Override;
use Stu\StuTestCase;

class ShipTest extends StuTestCase
{
    private ShipInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->subject = new Ship();
    }

    public function testsetLocationWhenMap(): void
    {
        $map = $this->mock(MapInterface::class);

        $this->subject->setLocation($map);

        $this->assertSame($map, $this->subject->getMap());
        $this->assertNull($this->subject->getStarsystemMap());
    }

    public function testsetLocationWhenSystemMapAndNotWormhole(): void
    {
        $map = $this->mock(MapInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);

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
        $map = $this->mock(MapInterface::class);
        $systemMap = $this->mock(StarSystemMapInterface::class);

        $systemMap->shouldReceive('getSystem->getMap')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->setLocation($map);
        $this->subject->setLocation($systemMap);

        $this->assertSame($systemMap, $this->subject->getStarsystemMap());
        $this->assertNull($this->subject->getMap());
    }
}
