<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use ArrayIterator;
use Mockery;
use Mockery\Adapter\Phpunit\MockeryTestCase;
use Mockery\MockInterface;
use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceJobInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\AllianceJobRepositoryInterface;
use Doctrine\Common\Collections\Collection;

class AllianceDeletionHandlerTest extends MockeryTestCase
{
    /** @var AllianceJobRepositoryInterface&MockInterface */
    private $allianceJobRepository;
    /** @var AllianceActionManagerInterface&MockInterface */
    private $allianceActionManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->allianceJobRepository = Mockery::mock(AllianceJobRepositoryInterface::class);

        $this->allianceActionManager = Mockery::mock(AllianceActionManagerInterface::class);

        $this->handler = new AllianceDeletionHandler(
            $this->allianceJobRepository,
            $this->allianceActionManager
        );
    }

    public function testDeleteDoesNotTouchAllianceIfNotFounder(): void
    {
        /** @var UserInterface|MockInterface $user */
        $user = Mockery::mock(UserInterface::class);

        /** @var AllianceJobInterface|MockInterface $job */
        $job = Mockery::mock(AllianceJobInterface::class);

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
            ->andReturn(666);

        $this->handler->delete($user);
    }

    public function testDeleteDeletesAllianceIfNoSuccessor(): void
    {
        /** @var UserInterface|MockInterface $user */
        $user = Mockery::mock(UserInterface::class);

        /** @var AllianceJobInterface|MockInterface $job */
        $job = Mockery::mock(AllianceJobInterface::class);

        /** @var AllianceInterface|MockInterface $alliance */
        $alliance = Mockery::mock(AllianceInterface::class);

        /** @var Collection<int, UserInterface>|MockInterface $members */
        $members = Mockery::mock(Collection::class);

        $userId = 666;
        $allianceId = 42;

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
            ->andReturn(AllianceEnum::ALLIANCE_JOBS_FOUNDER);
        $job->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);
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
            ->with($allianceId, true)
            ->once();

        $this->handler->delete($user);
    }

    public function testDeleteMakesSuccessorFounder(): void
    {
        /** @var UserInterface|MockInterface $user */
        $user = Mockery::mock(UserInterface::class);

        /** @var AllianceJobInterface|MockInterface $job */
        $job = Mockery::mock(AllianceJobInterface::class);

        /** @var AllianceJobInterface|MockInterface $successorJob */
        $successorJob = Mockery::mock(AllianceJobInterface::class);

        /** @var AllianceInterface|MockInterface $alliance */
        $alliance = Mockery::mock(AllianceInterface::class);

        /** @var Collection<int, UserInterface>|MockInterface $members */
        $members = Mockery::mock(Collection::class);

        $userId = 666;
        $successorUserId = 33;
        $allianceId = 42;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->allianceJobRepository->shouldReceive('getByUser')
            ->with($userId)
            ->once()
            ->andReturn([$job]);
        $this->allianceJobRepository->shouldReceive('delete')
            ->with($successorJob)
            ->once();

        $job->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn(AllianceEnum::ALLIANCE_JOBS_FOUNDER);
        $job->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);
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

        $successorJob->shouldReceive('getUserId')
            ->withNoArgs()
            ->once()
            ->andReturn($successorUserId);

        $this->allianceActionManager->shouldReceive('setJobForUser')
            ->with($allianceId, $successorUserId, AllianceEnum::ALLIANCE_JOBS_FOUNDER)
            ->once();

        $this->handler->delete($user);
    }
}
