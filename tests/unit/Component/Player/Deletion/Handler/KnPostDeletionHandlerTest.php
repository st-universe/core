<?php

declare(strict_types=1);

namespace Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Deletion\Handler\KnPostDeletionHandler;
use Stu\Orm\Entity\KnPostInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class KnPostDeletionHandlerTest extends StuTestCase
{
    /** @var MockInterface&KnPostRepositoryInterface */
    private MockInterface $knPostRepository;

    /** @var MockInterface&UserRepositoryInterface */
    private MockInterface $userRepository;

    private KnPostDeletionHandler $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->knPostRepository = $this->mock(KnPostRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);

        $this->subject = new KnPostDeletionHandler(
            $this->knPostRepository,
            $this->userRepository,
        );
    }

    public function testDeleteUpdatesKnItemUser(): void
    {
        $user = $this->mock(UserInterface::class);
        $fallbackUser = $this->mock(UserInterface::class);
        $knPost = $this->mock(KnPostInterface::class);

        $userId = 666;
        $userName = 'sixsixsix';

        $this->userRepository->shouldReceive('getFallbackUser')
            ->withNoArgs()
            ->once()
            ->andReturn($fallbackUser);

        $this->knPostRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$knPost]);
        $this->knPostRepository->shouldReceive('save')
            ->with($knPost)
            ->once();

        $knPost->shouldReceive('setUser')
            ->with($fallbackUser)
            ->once();
        $knPost->shouldReceive('setUsername')
            ->with($userName)
            ->once();

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('getName')
            ->withNoArgs()
            ->once()
            ->andReturn($userName);

        $this->subject->delete($user);
    }
}
