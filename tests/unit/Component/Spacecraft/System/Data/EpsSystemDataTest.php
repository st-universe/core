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

class EpsSystemDataTest extends StuTestCase
{
    private MockInterface&SpacecraftSystemRepositoryInterface $shipSystemRepository;
    private MockInterface&StatusBarFactoryInterface $statusBarFactory;

    /**
     * @var MockInterface&Ship
     */
    private $ship;

    private EpsSystemData $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->ship = $this->mock(Ship::class);

        $this->subject = new EpsSystemData(
            $this->shipSystemRepository,
            $this->statusBarFactory
        );
    }

    public function testGetEpsPercentage(): void
    {
        $system = $this->mock(SpacecraftSystem::class);

        $this->subject->setSpacecraft($this->ship);
        $this->subject->setEps(80);
        $this->subject->setMaxEps(100);

        $this->ship->shouldReceive('getSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::EPS)
            ->andReturn($system);
        $system->shouldReceive('getStatus')
            ->withNoArgs()
            ->andReturn(100);

        $result = $this->subject->getEpsPercentage();

        $this->assertEquals(80, $result);
    }
}
