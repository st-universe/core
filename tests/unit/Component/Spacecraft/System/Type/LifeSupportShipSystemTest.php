<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class LifeSupportShipSystemTest extends StuTestCase
{
    private MockInterface&Ship $ship;
    private MockInterface&ShipWrapperInterface $wrapper;

    private SpacecraftSystemTypeInterface $system;

    #[Override]
    public function setUp(): void
    {
        $this->ship = $this->mock(Ship::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->ship);

        $this->system = new LifeSupportShipSystem();
    }

    public function testCanBeActivatedWithInsufficientCrew(): void
    {
        $result = $this->system->canBeActivatedWithInsufficientCrew($this->wrapper);

        $this->assertTrue($result);
    }
}
