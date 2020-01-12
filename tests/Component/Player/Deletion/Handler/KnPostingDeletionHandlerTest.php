<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Component\Game\GameEnum;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class KnPostingDeletionHandlerTest extends MockeryTestCase
{

    /**
     * @var null|MockInterface|KnPostRepositoryInterface
     */
    private $knPostRepository;

    /**
     * @var null|MockInterface|UserRepositoryInterface
     */
    private $userRepository;

    /**
     * @var null|KnPostDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->knPostRepository = Mockery::mock(KnPostRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->handler = new KnPostDeletionHandler(
            $this->knPostRepository,
            $this->userRepository
        );
    }

    public function testDeleteSetsSystemUser(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $gameFallbackUser = Mockery::mock(UserInterface::class);
        $knPost = Mockery::mock(KnPostInterface::class);

        $userId = 666;
        $userName = 'some-old-user';

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('getUserName')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);

        $this->knPostRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$knPost]);
        $this->knPostRepository->shouldReceive('save')
            ->with($knPost)
            ->once();

        $this->userRepository->shouldReceive('find')
            ->with(GameEnum::USER_NOONE)
            ->once()
            ->andReturn($gameFallbackUser);

        $knPost->shouldReceive('setUsername')
            ->with($userName)
            ->once();
        $knPost->shouldReceive('setUser')
            ->with($gameFallbackUser)
            ->once();

        $this->handler->delete($user);
    }
}
