<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Mockery\MockInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Orm\Entity\AwardInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class MapCycleTest extends StuTestCase
{
    /** @var MockInterface&MapRepositoryInterface */
    private MockInterface $mapRepository;

    /** @var MockInterface&UserLayerRepositoryInterface */
    private MockInterface $userLayerRepository;

    /** @var MockInterface&UserMapRepositoryInterface */
    private MockInterface $userMapRepository;

    /** @var MockInterface&CreateUserAwardInterface */
    private MockInterface $createUserAward;

    private MaintenanceHandlerInterface $subject;

    protected function setUp(): void
    {
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->userLayerRepository = $this->mock(UserLayerRepositoryInterface::class);
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);
        $this->createUserAward = $this->mock(CreateUserAwardInterface::class);

        $this->subject = new MapCycle(
            $this->mapRepository,
            $this->userLayerRepository,
            $this->userMapRepository,
            $this->createUserAward
        );
    }

    public function testHandleExpectNothingIfNoUserLayerOnInsert(): void
    {
        $this->userLayerRepository->shouldReceive('getByMappingType')
            ->with(MapEnum::MAPTYPE_INSERT)
            ->once()
            ->andReturn([]);

        $this->subject->handle();
    }

    public function testHandleExpectNothingIfUserLayerNotMapped(): void
    {
        $userLayer = $this->mock(UserLayerInterface::class);
        $user = $this->mock(UserInterface::class);
        $layer = $this->mock(LayerInterface::class);

        $userLayer->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $userLayer->shouldReceive('getLayer')
            ->withNoArgs()
            ->once()
            ->andReturn($layer);

        $this->userLayerRepository->shouldReceive('getByMappingType')
            ->with(MapEnum::MAPTYPE_INSERT)
            ->once()
            ->andReturn([$userLayer]);

        $this->mapRepository->shouldReceive('getAmountByLayer')
            ->with($layer)
            ->once()
            ->andReturn(42);

        $this->userMapRepository->shouldReceive('getAmountByUser')
            ->with($user, $layer)
            ->once()
            ->andReturn(41);

        $this->subject->handle();
    }

    public function testHandleExpectLayerExploredIfUserLayerHasMapped(): void
    {
        $userLayer = $this->mock(UserLayerInterface::class);
        $user = $this->mock(UserInterface::class);
        $layer = $this->mock(LayerInterface::class);

        $userLayer->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $userLayer->shouldReceive('getLayer')
            ->withNoArgs()
            ->andReturn($layer);
        $userLayer->shouldReceive('setMappingType')
            ->with(MapEnum::MAPTYPE_LAYER_EXPLORED)
            ->once();

        $layer->shouldReceive('getAward')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->userLayerRepository->shouldReceive('getByMappingType')
            ->with(MapEnum::MAPTYPE_INSERT)
            ->once()
            ->andReturn([$userLayer]);
        $this->userLayerRepository->shouldReceive('save')
            ->with($userLayer)
            ->once();

        $this->mapRepository->shouldReceive('getAmountByLayer')
            ->with($layer)
            ->once()
            ->andReturn(42);

        $this->userMapRepository->shouldReceive('getAmountByUser')
            ->with($user, $layer)
            ->once()
            ->andReturn(42);
        $this->userMapRepository->shouldReceive('truncateByUserAndLayer')
            ->with($userLayer)
            ->once();

        $this->subject->handle();
    }

    public function testHandleExpectAwardCreationIfLayerHasAward(): void
    {
        $userLayer = $this->mock(UserLayerInterface::class);
        $user = $this->mock(UserInterface::class);
        $layer = $this->mock(LayerInterface::class);
        $award = $this->mock(AwardInterface::class);

        $userLayer->shouldReceive('getUser')
            ->withNoArgs()
            ->andReturn($user);
        $userLayer->shouldReceive('getLayer')
            ->withNoArgs()
            ->andReturn($layer);
        $userLayer->shouldReceive('setMappingType')
            ->with(MapEnum::MAPTYPE_LAYER_EXPLORED)
            ->once();

        $layer->shouldReceive('getAward')
            ->withNoArgs()
            ->once()
            ->andReturn($award);

        $this->userLayerRepository->shouldReceive('getByMappingType')
            ->with(MapEnum::MAPTYPE_INSERT)
            ->once()
            ->andReturn([$userLayer]);
        $this->userLayerRepository->shouldReceive('save')
            ->with($userLayer)
            ->once();

        $this->mapRepository->shouldReceive('getAmountByLayer')
            ->with($layer)
            ->once()
            ->andReturn(42);

        $this->userMapRepository->shouldReceive('getAmountByUser')
            ->with($user, $layer)
            ->once()
            ->andReturn(42);
        $this->userMapRepository->shouldReceive('truncateByUserAndLayer')
            ->with($userLayer)
            ->once();

        $this->createUserAward->shouldReceive('createAwardForUser')
            ->with($user, $award)
            ->once();

        $this->subject->handle();
    }
}
