<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery\MockInterface;
use Stu\Exception\AccessViolationException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class UserBoundedSpacecraftLoaderTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&StuConfigInterface $stuConfig;
    private MockInterface&LockManagerInterface $lockManager;
    private MockInterface&Spacecraft $spacecraft;
    private MockInterface&SpacecraftWrapperInterface $wrapper;

    private UserBoundedSpacecraftLoader $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);
        $this->spacecraft = $this->mock(Spacecraft::class);
        $this->wrapper = $this->mock(SpacecraftWrapperInterface::class);

        $this->subject = new UserBoundedSpacecraftLoader(
            $this->spacecraftRepository,
            $this->userRepository,
            $this->crewAssignmentRepository,
            $this->spacecraftWrapperFactory,
            $this->stuConfig,
            $this->lockManager
        );

        $this->stuConfig->shouldReceive('getDbSettings->useSqlite')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(false);
    }

    public function testGetByIdAndUserLocksAffectedUsersInOrderAndReloads(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with(5, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5])
            ->once()
            ->andReturn([42, 17]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([17, 42])
            ->once();

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->spacecraft);

        static::assertSame($this->spacecraft, $this->subject->getByIdAndUser(5, 42));
    }

    public function testGetByIdAndUserThrowsWhenSpacecraftDoesNotExist(): void
    {
        $this->expectException(SpacecraftDoesNotExistException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with(5, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5])
            ->once()
            ->andReturn([42]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([42])
            ->once();

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn(null);

        $this->subject->getByIdAndUser(5, 42);
    }

    public function testGetWrapperByIdAndUserAndTargetUserLocksTheTargetUserToo(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with(5, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5])
            ->once()
            ->andReturn([42]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([7, 42])
            ->once();

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);

        static::assertSame($this->wrapper, $this->subject->getWrapperByIdAndUserAndTargetUser(5, 42, 7));
    }

    public function testGetByIdAndUserThrowsWhenUserDoesNotOwnSpacecraft(): void
    {
        $this->expectException(AccessViolationException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with(5, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5])
            ->once()
            ->andReturn([42]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([42])
            ->once();

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->twice()
            ->andReturn(42);

        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 99)
            ->once()
            ->andReturn(false);

        $this->subject->getByIdAndUser(5, 99);
    }

    public function testGetByIdAndUserUsesCacheOnSecondCall(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with(5, LockTypeEnum::SHIP_GROUP)
            ->twice()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5])
            ->once()
            ->andReturn([42]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([42])
            ->once();

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);

        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->twice()
            ->andReturn($this->spacecraft);

        // first call populates cache
        static::assertSame($this->spacecraft, $this->subject->getByIdAndUser(5, 42));

        // second call should use cache: repository/find/wrap called only once, wrapper->get() still returns spacecraft
        static::assertSame($this->spacecraft, $this->subject->getByIdAndUser(5, 42));
    }
}
