<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLayer;
use Stu\Orm\Repository\UserLayerRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class UserMapDeletionHandlerTest extends StuTestCase
{
    private null|MockInterface|UserMapRepositoryInterface $userMapRepository;

    private null|MockInterface|UserLayerRepositoryInterface $userLayerRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[\Override]
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
        $user = $this->mock(User::class);
        $userLayer = $this->mock(UserLayer::class);
        $userLayers = new ArrayCollection([$userLayer]);

        $user->shouldReceive('getUserLayers')
            ->withNoArgs()
            ->once()
            ->andReturn($userLayers);

        $this->userMapRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->userLayerRepository->shouldReceive('delete')
            ->with($userLayer)
            ->once();

        $this->handler->delete($user);
    }
}
