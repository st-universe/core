<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use Mockery\MockInterface;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Stu\StuTestCase;

final class UserCrewRankDeletionHandlerTest extends StuTestCase
{
    private MockInterface&UserCrewRankRepositoryInterface $userCrewRankRepository;

    private UserCrewRankDeletionHandler $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userCrewRankRepository = $this->mock(UserCrewRankRepositoryInterface::class);
        $this->subject = new UserCrewRankDeletionHandler($this->userCrewRankRepository);
    }

    public function testDeletesCrewRankNames(): void
    {
        $user = $this->mock(User::class);

        $this->userCrewRankRepository->shouldReceive('truncateByUser')
            ->with($user)
            ->once();

        $this->subject->delete($user);
    }
}
