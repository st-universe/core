<?php

declare(strict_types=1);

namespace Stu\Module\Maintenance;

use Mockery\MockInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Award\Lib\CreateUserAwardInterface;
use Stu\Orm\Entity\Award;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class MapCycleTest extends StuTestCase
{
    private MockInterface&MapRepositoryInterface $mapRepository;

    private MockInterface&UserLayerRepositoryInterface $userLayerRepository;

    private MockInterface&UserMapRepositoryInterface $userMapRepository;

    private MockInterface&CreateUserAwardInterface $createUserAward;

    private MaintenanceHandlerInterface $subject;

    #[\Override]
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
        $userLayer = $this->mock(UserLayer::class);
        $user = $this->mock(User::class);
        $layer = $this->mock(Layer::class);

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
        $userLayer = $this->mock(UserLayer::class);
        $user = $this->mock(User::class);
        $layer = $this->mock(Layer::class);

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
        $userLayer = $this->mock(UserLayer::class);
        $user = $this->mock(User::class);
        $layer = $this->mock(Layer::class);
        $award = $this->mock(Award::class);

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
