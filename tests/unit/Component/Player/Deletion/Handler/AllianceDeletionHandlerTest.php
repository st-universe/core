<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion\Handler;

use ArrayIterator;
use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Alliance\Lib\AllianceActionManagerInterface;
use Stu\Module\Alliance\Lib\AllianceJobManagerInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceJob;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserRepositoryInterface;
use Doctrine\Common\Collections\Collection;
use Stu\StuTestCase;

class AllianceDeletionHandlerTest extends StuTestCase
{
    private AllianceActionManagerInterface&MockInterface $allianceActionManager;
    private UserRepositoryInterface&MockInterface $userRepository;
    private AllianceJobManagerInterface&MockInterface $allianceJobManager;

    private PlayerDeletionHandlerInterface $handler;

    #[Override]
    public function setUp(): void
    {
        $this->allianceActionManager = Mockery::mock(AllianceActionManagerInterface::class);
        $this->userRepository = Mockery::mock(UserRepositoryInterface::class);
        $this->allianceJobManager = Mockery::mock(AllianceJobManagerInterface::class);

        $this->handler = new AllianceDeletionHandler(
            $this->allianceActionManager,
            $this->userRepository,
            $this->allianceJobManager
        );
    }

    public function testDeleteDoesNotTouchAllianceIfNotFounder(): void
    {
        $user = Mockery::mock(User::class);
        $alliance = Mockery::mock(Alliance::class);

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);

        $this->allianceJobManager->shouldReceive('hasUserFounderPermission')
            ->with($user, $alliance)
            ->once()
            ->andReturnFalse();

        $this->allianceJobManager->shouldReceive('removeUserFromAllJobs')
            ->with($user, $alliance)
            ->once();

        $this->handler->delete($user);
    }

    public function testDeleteDeletesAllianceIfNoSuccessor(): void
    {
        $user = Mockery::mock(User::class);
        $alliance = Mockery::mock(Alliance::class);
        $members = Mockery::mock(Collection::class);

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $user->shouldReceive('setAlliance')
            ->with(null)
            ->once();

        $this->allianceJobManager->shouldReceive('hasUserFounderPermission')
            ->with($user, $alliance)
            ->once()
            ->andReturnTrue();

        $alliance->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);

        $members->shouldReceive('removeElement')
            ->with($user)
            ->once();
        $members->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturnTrue();

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->once();

        $this->allianceJobManager->shouldReceive('removeUserFromAllJobs')
            ->with($user, $alliance)
            ->once();

        $this->allianceActionManager->shouldReceive('delete')
            ->with($alliance)
            ->once();

        $this->handler->delete($user);
    }

    public function testDeleteMakesSuccessorFounder(): void
    {
        $user = $this->mock(User::class);
        $successorUser = $this->mock(User::class);
        $founderJob = $this->mock(AllianceJob::class);
        $successorJob = $this->mock(AllianceJob::class);
        $alliance = $this->mock(Alliance::class);
        $members = $this->mock(Collection::class);

        $user->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $user->shouldReceive('setAlliance')
            ->with(null)
            ->once();

        $this->allianceJobManager->shouldReceive('hasUserFounderPermission')
            ->with($user, $alliance)
            ->once()
            ->andReturnTrue();

        $alliance->shouldReceive('getMembers')
            ->withNoArgs()
            ->once()
            ->andReturn($members);
        $alliance->shouldReceive('getFounder')
            ->withNoArgs()
            ->once()
            ->andReturn($founderJob);
        $alliance->shouldReceive('getSuccessor')
            ->withNoArgs()
            ->once()
            ->andReturn($successorJob);

        $members->shouldReceive('removeElement')
            ->with($user)
            ->once();
        $members->shouldReceive('isEmpty')
            ->withNoArgs()
            ->once()
            ->andReturnFalse();

        $successorJob->shouldReceive('getUsers')
            ->withNoArgs()
            ->once()
            ->andReturn([$successorUser]);

        $successorUser->shouldReceive('getLastaction')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(123456);

        $this->userRepository->shouldReceive('save')
            ->with($user)
            ->once();

        $this->allianceJobManager->shouldReceive('removeUserFromJob')
            ->with($user, $founderJob)
            ->once();
        $this->allianceJobManager->shouldReceive('removeUserFromAllJobs')
            ->with($successorUser, $alliance)
            ->once();
        $this->allianceJobManager->shouldReceive('assignUserToJob')
            ->with($successorUser, $founderJob)
            ->once();

        $this->handler->delete($user);
    }
}
