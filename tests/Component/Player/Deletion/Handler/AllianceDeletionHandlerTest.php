<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

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

class AllianceDeletionHandlerTest extends MockeryTestCase
{
    /**
     * @var null|MockInterface|AllianceJobRepositoryInterface
     */
    private $allianceJobRepository;

    /**
     * @var null|MockInterface|AllianceActionManagerInterface
     */
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
        $user = Mockery::mock(UserInterface::class);
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
        $user = Mockery::mock(UserInterface::class);
        $job = Mockery::mock(AllianceJobInterface::class);
        $alliance = Mockery::mock(AllianceInterface::class);

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

        $this->allianceActionManager->shouldReceive('delete')
            ->with($allianceId, false)
            ->once();

        $this->handler->delete($user);
    }

    public function testDeleteMakesSuccessorFounder(): void
    {
        $user = Mockery::mock(UserInterface::class);
        $job = Mockery::mock(AllianceJobInterface::class);
        $successorJob = Mockery::mock(AllianceJobInterface::class);
        $alliance = Mockery::mock(AllianceInterface::class);

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
