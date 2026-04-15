<?php

declare(strict_types=1);

namespace Stu\Module\Building\Action;

use Mockery\MockInterface;
use Psr\Container\ContainerInterface;
use Stu\Component\Building\BuildingFunctionEnum;
use Stu\StuTestCase;

class BuildingFunctionActionMapperTest extends StuTestCase
{
    private MockInterface&ContainerInterface $container;
    private MockInterface&BuildingActionHandlerInterface $handler;

    private BuildingFunctionActionMapper $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->container = $this->mock(ContainerInterface::class);
        $this->handler = $this->mock(BuildingActionHandlerInterface::class);

        $this->subject = new BuildingFunctionActionMapper($this->container);
    }

    public function testMapReturnsShipyardHandlerForShipyardFunctions(): void
    {
        $this->container->shouldReceive('get')
            ->with(Shipyard::class)
            ->times(5)
            ->andReturn($this->handler);

        foreach ([
            BuildingFunctionEnum::FIGHTER_SHIPYARD,
            BuildingFunctionEnum::ESCORT_SHIPYARD,
            BuildingFunctionEnum::FRIGATE_SHIPYARD,
            BuildingFunctionEnum::CRUISER_SHIPYARD,
            BuildingFunctionEnum::DESTROYER_SHIPYARD
        ] as $buildingFunction) {
            $this->assertSame($this->handler, $this->subject->map($buildingFunction));
        }
    }
}
