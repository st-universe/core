<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Data;

use Mockery\MockInterface;
use Override;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\SpacecraftSystemRepositoryInterface;
use Stu\StuTestCase;

class EpsSystemDataTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftSystemRepositoryInterface */
    private $shipSystemRepository;
    /** @var MockInterface&StatusBarFactoryInterface */
    private $statusBarFactory;

    /**
     * @var MockInterface&ShipInterface
     */
    private $ship;

    private EpsSystemData $subject;

    #[Override]
    public function setUp(): void
    {
        $this->shipSystemRepository = $this->mock(SpacecraftSystemRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->ship = $this->mock(ShipInterface::class);

        $this->subject = new EpsSystemData(
            $this->shipSystemRepository,
            $this->statusBarFactory
        );
    }

    public function testGetEpsPercentage(): void
    {
        $this->subject->setSpacecraft($this->ship);
        $this->subject->setEps(80);
        $this->subject->setMaxEps(100);

        $this->ship->shouldReceive('hasShipSystem')
            ->with(SpacecraftSystemTypeEnum::SYSTEM_EPS)
            ->andReturn(false);

        $result = $this->subject->getEpsPercentage();

        $this->assertEquals(80, $result);
    }
}
