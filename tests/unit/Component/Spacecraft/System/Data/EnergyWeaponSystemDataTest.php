<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftSystemInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class EnergyWeaponSystemDataTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftSystemRepositoryInterface */
    private $shipSystemRepository;
    /** @var MockInterface&StatusBarFactoryInterface */
    private $statusBarFactory;

    /** @var MockInterface&ShipInterface */
    private $ship;

    private EnergyWeaponSystemData $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new EnergyWeaponSystemData(
            $this->shipSystemRepository,
            $this->statusBarFactory
        );
    }

    public function testGetBaseDamage(): void
    {
        $spacecraftSystem = $this->mock(SpacecraftSystemInterface::class);

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
