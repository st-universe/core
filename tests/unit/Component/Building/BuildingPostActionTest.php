<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingFunction;
use Stu\Orm\Entity\Building;
use Stu\Orm\Entity\Colony;
use Stu\StuTestCase;

class BuildingPostActionTest extends StuTestCase
{
    private MockInterface&BuildingFunctionActionMapperInterface $buildingFunctionActionMapper;

    private BuildingPostAction $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->buildingFunctionActionMapper = $this->mock(BuildingFunctionActionMapperInterface::class);

        $this->subject = new BuildingPostAction(
            $this->buildingFunctionActionMapper,
        );
    }

    public function testHandleDeactivationPerformsActions(): void
    {
        $building = $this->mock(Building::class);
        $colony = $this->mock(Colony::class);
        $function = $this->mock(BuildingFunction::class);
        $buildingActionHandler = $this->mock(BuildingActionHandlerInterface::class);

        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingFunction);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($buildingFunction)
            ->once()
            ->andReturn($buildingActionHandler);

        $buildingActionHandler->shouldReceive('deactivate')
            ->with($buildingFunction, $colony)
            ->once();

        $this->subject->handleDeactivation($building, $colony);
    }

    public function testHandleActivationPerformsActions(): void
    {
        $building = $this->mock(Building::class);
        $colony = $this->mock(Colony::class);
        $function = $this->mock(BuildingFunction::class);
        $buildingActionHandler = $this->mock(BuildingActionHandlerInterface::class);

        $buildingFunction = BuildingFunctionEnum::SHIELD_BATTERY;

        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($buildingFunction);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($buildingFunction)
            ->once()
            ->andReturn($buildingActionHandler);

        $buildingActionHandler->shouldReceive('activate')
            ->with($buildingFunction, $colony)
            ->once();

        $this->subject->handleActivation($building, $colony);
    }
}
