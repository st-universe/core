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

    public function testSetLocationSyncsLocationIdWhenLocationIsPersisted(): void
    {
        $map = new Map();
        $this->setPrivateProperty(Location::class, $map, 'id', 42);

        $this->subject->setLocation($map);

        $this->assertSame(42, $this->getPrivateProperty(Spacecraft::class, $this->subject, 'locationId'));
    }

    public function testSetFleetSyncsFleetIdWhenFleetIsPersisted(): void
    {
        $fleet = new Fleet();
        $this->setPrivateProperty(Fleet::class, $fleet, 'id', 23);

        $this->subject->setFleet($fleet);

        $this->assertSame(23, $this->subject->getFleetId());
    }

    public function testSetFleetNullClearsFleetId(): void
    {
        $this->setPrivateProperty(Ship::class, $this->subject, 'fleet_id', 23);

        $this->subject->setFleet(null);

        $this->assertNull($this->subject->getFleetId());
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
    }

    private function setPrivateProperty(string $class, object $object, string $property, mixed $value): void
    {
        $reflection = new \ReflectionProperty($class, $property);
        $reflection->setValue($object, $value);
    }

    private function getPrivateProperty(string $class, object $object, string $property): mixed
    {
        $reflection = new \ReflectionProperty($class, $property);

        return $reflection->getValue($object);
    }
}
