<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftSystem;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class EnergyWeaponSystemDataTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemRepositoryInterface $shipSystemRepository;
    private MockInterface&StatusBarFactoryInterface $statusBarFactory;

    private MockInterface&Ship $ship;

    private EnergyWeaponSystemData $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->ship = $this->mock(Ship::class);

        $this->subject = new EnergyWeaponSystemData(
            $this->shipSystemRepository,
            $this->statusBarFactory
        );
    }

    public function testGetBaseDamage(): void
    {
        $spacecraftSystem = $this->mock(SpacecraftSystem::class);

        $this->subject->setSpacecraft($this->ship);
        $this->subject->setBaseDamage(200);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::PHASER)
            ->andReturn($spacecraftSystem);

        $spacecraftSystem->shouldReceive('getStatus')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->getBaseDamage();

        $this->assertEquals(84, $result);
    }
}
