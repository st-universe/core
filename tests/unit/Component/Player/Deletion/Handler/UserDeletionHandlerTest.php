<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserLock;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserLockRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class UserDeletionHandlerTest extends StuTestCase
{
    /**
     * @var null|MockInterface|SessionStringRepositoryInterface
     */
    private $sessionStringRepository;

    /**
     * @var null|MockInterface
     */
    private $userProfileVisitorRepository;

    /**
     * @var null|MockInterface
     */
    private $userRepository;

    /**
     * @var null|MockInterface|UserLockRepositoryInterface
     */
    private $userLockRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->sessionStringRepository = $this->mock(SessionStringRepositoryInterface::class);
        $this->userProfileVisitorRepository = $this->mock(UserProfileVisitorRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->userLockRepository = $this->mock(UserLockRepositoryInterface::class);

        $this->handler = new UserDeletionHandler(
            $this->sessionStringRepository,
            $this->userProfileVisitorRepository,
            $this->userRepository,
            $this->userLockRepository
        );
    }

    public function testDeleteDeletesUser(): void
    {
        $user = $this->mock(User::class);
        $userLock = $this->mock(UserLock::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $user->shouldReceive('getUserLock')
            ->withNoArgs()
            ->once()
            ->andReturn($userLock);

        $userLock->shouldReceive('setUser')
            ->with(null)
            ->once();
        $userLock->shouldReceive('setUserId')
            ->with(null)
            ->once();
        $userLock->shouldReceive('setFormerUserId')
            ->with(42)
            ->once();

        $this->userLockRepository->shouldReceive('save')
            ->with($userLock)
            ->once();

        $this->sessionStringRepository->shouldReceive('truncate')
            ->with($user)
            ->once();

        $this->userProfileVisitorRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->userRepository->shouldReceive('delete')
            ->with($user)
            ->once();


        $this->handler->delete($user);
    }
}
