<?php

declare(strict_types=1);

namespace Stu\Component\Building;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use Stu\Module\Building\Action\BuildingActionHandlerInterface;
use Stu\Module\Building\Action\BuildingFunctionActionMapperInterface;
use Stu\Orm\Entity\BuildingFunctionInterface;
use Stu\Orm\Entity\BuildingInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\StuTestCase;

class BuildingPostActionTest extends StuTestCase
{
    /** @var MockInterface&BuildingFunctionActionMapperInterface */
    private MockInterface $buildingFunctionActionMapper;

    private BuildingPostAction $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->buildingFunctionActionMapper = $this->mock(BuildingFunctionActionMapperInterface::class);

        $this->subject = new BuildingPostAction(
            $this->buildingFunctionActionMapper,
        );
    }

    public function testHandleDeactivationPerformsActions(): void
    {
        $building = $this->mock(BuildingInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $function = $this->mock(BuildingFunctionInterface::class);
        $buildingActionHandler = $this->mock(BuildingActionHandlerInterface::class);

        $functionId = 666;

        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($functionId);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($functionId)
            ->once()
            ->andReturn($buildingActionHandler);

        $buildingActionHandler->shouldReceive('deactivate')
            ->with($functionId, $colony)
            ->once();

        $this->subject->handleDeactivation($building, $colony);
    }

    public function testHandleActivationPerformsActions(): void
    {
        $building = $this->mock(BuildingInterface::class);
        $colony = $this->mock(ColonyInterface::class);
        $function = $this->mock(BuildingFunctionInterface::class);
        $buildingActionHandler = $this->mock(BuildingActionHandlerInterface::class);

        $functionId = 666;

        $building->shouldReceive('getFunctions')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$function]));

        $function->shouldReceive('getFunction')
            ->withNoArgs()
            ->once()
            ->andReturn($functionId);

        $this->buildingFunctionActionMapper->shouldReceive('map')
            ->with($functionId)
            ->once()
            ->andReturn($buildingActionHandler);

        $buildingActionHandler->shouldReceive('activate')
            ->with($functionId, $colony)
            ->once();

        $this->subject->handleActivation($building, $colony);
    }
}
