<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\SessionStringRepositoryInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;

class UserDeletionHandlerTest extends MockeryTestCase
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
     * @var null|UserDeletionHandler
     */
    private $handler;

    public function setUp(): void
    {
        $this->sessionStringRepository = Mockery::mock(SessionStringRepositoryInterface::class);
        $this->userProfileVisitorRepository = Mockery::mock(UserProfileVisitorRepositoryInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->handler = new UserDeletionHandler(
            $this->sessionStringRepository,
            $this->userProfileVisitorRepository,
            $this->userRepository
        );
    }

    public function testDeleteDeletesUser(): void
    {
        $user = Mockery::mock(UserInterface::class);

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
