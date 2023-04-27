<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;

class UserMapDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|UserMapRepositoryInterface
     */
    private $userMapRepository;

    /**
     * @var null|UserMapDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->userMapRepository = $this->mock(UserMapRepositoryInterface::class);

        $this->handler = new UserMapDeletionHandler(
            $this->userMapRepository
        );
    }

    public function testDeleteDeletesUserMapEntries(): void
    {
        $user = Mockery::mock(UserInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userMapRepository->shouldReceive('truncateByUser')
            ->with(42)
            ->once();

        $this->handler->delete($user);
    }
}
