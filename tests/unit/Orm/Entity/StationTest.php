<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\StuTestCase;

class StationTest extends StuTestCase
{
    private Station $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->subject = new Station($this->mock(SpacecraftCondition::class));
    }

    public function testGetDockedWorkbeeCount(): void
    {
        $docked1 = $this->mock(Ship::class);
        $docked2 = $this->mock(Ship::class);
        $docked3 = $this->mock(Ship::class);
        $docked4 = $this->mock(Ship::class);

        $this->subject->getDockedShips()->add($docked1);
        $this->subject->getDockedShips()->add($docked2);
        $this->subject->getDockedShips()->add($docked3);
        $this->subject->getDockedShips()->add($docked4);

        $docked1->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked2->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked2->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $docked3->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked3->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked3->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $docked4->shouldReceive('hasEnoughCrew')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $docked4->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $docked4->shouldReceive('getRump->isWorkbee')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getDockedWorkbeeCount($this->subject);

        $this->assertEquals(1, $result);
    }
}
