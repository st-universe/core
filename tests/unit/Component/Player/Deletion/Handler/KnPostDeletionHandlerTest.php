<?php

declare(strict_types=1);

namespace Component\Player\Deletion\Handler;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use Override;
use Stu\Component\Player\Deletion\Handler\KnPostDeletionHandler;
use Stu\Orm\Entity\KnPost;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\KnPostRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class KnPostDeletionHandlerTest extends StuTestCase
{
    private MockInterface&KnPostRepositoryInterface $knPostRepository;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&EntityManagerInterface $entityManager;

    private KnPostDeletionHandler $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->knPostRepository = $this->mock(KnPostRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->entityManager = $this->mock(EntityManagerInterface::class);

        $this->subject = new KnPostDeletionHandler(
            $this->knPostRepository,
            $this->userRepository,
            $this->entityManager
        );
    }

    public function testDeleteUpdatesKnItemUser(): void
    {
        $user = $this->mock(User::class);
        $fallbackUser = $this->mock(User::class);
        $knPost = $this->mock(KnPost::class);

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

        $this->entityManager->shouldReceive('detach')
            ->with($knPost)
            ->once();

        $this->subject->delete($user);
    }
}
