<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use ArrayIterator;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Stu\Component\Alliance\Enum\AllianceJobTypeEnum;
use Stu\StuTestCase;

class AllianceDeletionHandlerTest extends StuTestCase
{
    private AllianceJobRepositoryInterface&MockInterface $allianceJobRepository;
    private AllianceActionManagerInterface&MockInterface $allianceActionManager;
    private UserRepositoryInterface&MockInterface $userRepository;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->allianceJobRepository = Mockery::mock(AllianceJobRepositoryInterface::class);
        $this->allianceActionManager = Mockery::mock(AllianceActionManagerInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);

        $this->handler = new AllianceDeletionHandler(
            $this->allianceJobRepository,
            $this->allianceActionManager,
            $this->userRepository
        );
    }

    public function testDeleteDoesNotTouchAllianceIfNotFounder(): void
    {
        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var AllianceJob|MockInterface $job */
        $job = Mockery::mock(AllianceJob::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->allianceJobRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$job]);
        $this->allianceJobRepository->shouldReceive('delete')
            ->with($job)
            ->once();

        $job->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(AllianceJobTypeEnum::DIPLOMATIC);

        $this->handler->delete($user);
    }

    public function testDeleteDeletesAllianceIfNoSuccessor(): void
    {
        /** @var User|MockInterface $user */
        $user = Mockery::mock(User::class);

        /** @var AllianceJob|MockInterface $job */
        $job = Mockery::mock(AllianceJob::class);

        /** @var Alliance|MockInterface $alliance */
        $alliance = Mockery::mock(Alliance::class);

        /** @var Collection<int, User>|MockInterface $members */
        $members = Mockery::mock(Collection::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('setAlliance')
            ->with(null)
            ->once();

        $this->allianceJobRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$job]);
        $this->allianceJobRepository->shouldReceive('delete')
            ->with($job)
            ->once();

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->once();

        $job->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(AllianceJobTypeEnum::FOUNDER);
        $job->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $alliance->shouldReceive('getSuccessor')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $alliance->shouldReceive('getDiplomatic')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $alliance->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);

        $members->shouldReceive('removeElement')
            ->with($user)
            ->once();
        $members->shouldReceive('getIterator')
            ->andReturn(new ArrayIterator([]));

        $this->allianceActionManager->shouldReceive('delete')
            ->with($alliance)
            ->once();

        $this->handler->delete($user);
    }

    public function testDeleteMakesSuccessorFounder(): void
    {
        $user = $this->mock(User::class);
        $successorUser = $this->mock(User::class);
        $job = $this->mock(AllianceJob::class);
        $successorJob = $this->mock(AllianceJob::class);
        $alliance = $this->mock(Alliance::class);
        $members = $this->mock(Collection::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $user->shouldReceive('setAlliance')
            ->with(null)
            ->once();

        $this->allianceJobRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$job]);
        $this->allianceJobRepository->shouldReceive('delete')
            ->with($successorJob)
            ->once();

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->once();

        $job->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(AllianceJobTypeEnum::FOUNDER);
        $job->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $alliance->shouldReceive('getSuccessor')
            ->withNoArgs()
            ->once()
            ->andReturn($successorJob);
        $alliance->shouldReceive('getDiplomatic')
            ->withNoArgs()
            ->once()
            ->andReturnNull();
        $alliance->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);

        $members->shouldReceive('removeElement')
            ->with($user)
            ->once();
        $members->shouldReceive('getIterator')
            ->andReturn(new ArrayIterator([]));

        $successorJob->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($successorUser);

        $this->allianceActionManager->shouldReceive('setJobForUser')
            ->with($alliance, $successorUser, AllianceJobTypeEnum::FOUNDER)
            ->once();

        $this->handler->delete($user);
    }
}
