<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserLayerInterface;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class UserMapDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|UserMapRepositoryInterface
     */
    private $userMapRepository;

    /**
     * @var null|MockInterface|UserLayerRepositoryInterface
     */
    private $userLayerRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);
        $this->userLayerRepository = $this->mock(UserLayerRepositoryInterface::class);

        $this->handler = new UserMapDeletionHandler(
            $this->userMapRepository,
            $this->userLayerRepository
        );
    }

    public function testDeleteDeletesUserMapEntries(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $userLayer = Mockery::mock(UserLayerInterface::class);
        $userLayers = new ArrayCollection([$userLayer]);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);
        $user->shouldReceive('getUserLayers')
            ->withNoArgs()
            ->once()
            ->andReturn($userLayers);

        $this->userMapRepository->shouldReceive('truncateByUser')
            ->with(42)
            ->once();

        $this->userLayerRepository->shouldReceive('delete')
            ->with($userLayer)
            ->once();

        $this->handler->delete($user);
    }
}
