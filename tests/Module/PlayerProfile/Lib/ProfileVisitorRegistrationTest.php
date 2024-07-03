<?php

declare(strict_types=1);

namespace Stu\Module\PlayerProfile\Lib;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Entity\UserProfileVisitorInterface;
use Stu\Orm\Repository\UserProfileVisitorRepositoryInterface;
use Stu\StuTestCase;

class ProfileVisitorRegistrationTest extends StuTestCase
{
    private MockInterface $userProfileVisitorRepository;

    private ProfileVisitorRegistration $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->userProfileVisitorRepository = $this->mock(UserProfileVisitorRepositoryInterface::class);

        $this->subject = new ProfileVisitorRegistration(
            $this->userProfileVisitorRepository
        );
    }

    public function testRegisterDoesNotRegisterIfUserAndVisitorAreSame(): void
    {
        $user = $this->mock(UserInterface::class);
        $visitor = $this->mock(UserInterface::class);

        $userId = 666;

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $visitor->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);

        $this->subject->register($user, $visitor);
    }

    public function testRegisterDoesNotRegisterIfVisitorHasAlreadyVisited(): void
    {
        $user = $this->mock(UserInterface::class);
        $visitor = $this->mock(UserInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $visitor->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userProfileVisitorRepository->shouldReceive('isVisitRegistered')
            ->with($user, $visitor)
            ->once()
            ->andReturnTrue();

        $this->subject->register($user, $visitor);
    }

    public function testRegisterRegistersVisit(): void
    {
        $user = $this->mock(UserInterface::class);
        $visitor = $this->mock(UserInterface::class);
        $profileVisit = $this->mock(UserProfileVisitorInterface::class);

        $user->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(666);

        $visitor->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->userProfileVisitorRepository->shouldReceive('isVisitRegistered')
            ->with($user, $visitor)
            ->once()
            ->andReturnFalse();
        $this->userProfileVisitorRepository->shouldReceive('prototype')
            ->withNoArgs()
            ->once()
            ->andReturn($profileVisit);
        $this->userProfileVisitorRepository->shouldReceive('save')
            ->with($profileVisit)
            ->once();

        $profileVisit->shouldReceive('setProfileUser')
            ->with($user)
            ->once()
            ->andReturnSelf();
        $profileVisit->shouldReceive('setUser')
            ->with($visitor)
            ->once()
            ->andReturnSelf();
        $profileVisit->shouldReceive('setDate')
            ->with(Mockery::type('int'))
            ->once()
            ->andReturnSelf();

        $this->subject->register($user, $visitor);
    }
}
