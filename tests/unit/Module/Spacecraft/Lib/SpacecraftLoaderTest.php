<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery\MockInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftLoaderTest extends StuTestCase
{
    private MockInterface&SpacecraftRepositoryInterface $spacecraftRepository;
    private MockInterface&UserRepositoryInterface $userRepository;
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&SpacecraftWrapperFactoryInterface $spacecraftWrapperFactory;
    private MockInterface&StuConfigInterface $stuConfig;
    private MockInterface&LockManagerInterface $lockManager;
    private MockInterface&Spacecraft $spacecraft;
    private MockInterface&SpacecraftWrapperInterface $wrapper;

    private int $spacecraftId = 5;
    private int $userId = 42;

    private SpacecraftLoader $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();
        SpacecraftLoader::clearCache();

        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->stuConfig = $this->mock(StuConfigInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);
        $this->spacecraft = $this->mock(Spacecraft::class);
        $this->wrapper = $this->mock(SpacecraftWrapperInterface::class);

        $this->subject = new SpacecraftLoader(
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

    public function testgGtByIdAndUserExpectErrorWhenEntityLocked(): void
    {
        static::expectExceptionMessage('Tick läuft gerade, Zugriff auf Schiff ist daher blockiert');
        static::expectException(EntityLockedException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(true);

        $this->subject->getByIdAndUser($this->spacecraftId, $this->userId);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipNonExistent(): void
    {
        $this->expectException(SpacecraftDoesNotExistException::class);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn(null);

        $this->subject->getByIdAndUser($this->spacecraftId, $this->userId, false, false);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipBelongsToOtherUser(): void
    {
        $this->expectException(AccessViolationException::class);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(false);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, false, false);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOperationUnallowedWithUplink(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, false, false);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfUplinkOffline(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getSystemState')
            ->with(SpacecraftSystemTypeEnum::UPLINK)
            ->once()
            ->andReturn(false);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(42);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, true, false);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOwnerOnVacation(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getSystemState')
            ->with(SpacecraftSystemTypeEnum::UPLINK)
            ->once()
            ->andReturn(true);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn(42);
        $this->spacecraft->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, true, false);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserSuccessful(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
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
            ->with($this->spacecraftId)
            ->once()
            ->andReturn($this->spacecraft);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn($this->userId);
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->spacecraft);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, $this->userId);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetWrapperByIdAndUserSuccessful(): void
    {
        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
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
            ->with($this->spacecraftId)
            ->once()
            ->andReturn($this->spacecraft);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->andReturn($this->userId);

        $result = $this->subject->getWrapperByIdAndUser($this->spacecraftId, $this->userId);

        $this->assertEquals($this->wrapper, $result);
    }

    public function testgetWrappersBySourceAndUserAndTargetAwaitTargetWrapperIsNotNull(): void
    {
        $target = $this->mock(Ship::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('getUserIdsForSpacecrafts')
            ->with([5, 1])
            ->once()
            ->andReturn([42, 999]);

        $this->userRepository->shouldReceive('lockUsersForUpdate')
            ->with([42, 999])
            ->once();

        //ship
        $this->spacecraftRepository->shouldReceive('find')
            ->with($this->spacecraftId)
            ->once()
            ->andReturn($this->spacecraft);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->userId);

        //target
        $this->spacecraftRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($target);
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($target)
            ->once()
            ->andReturn($targetWrapper);
        $target->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(999);
        $target->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn(1);

        $result = $this->subject->getWrappersBySourceAndUserAndTarget($this->spacecraftId, $this->userId, 1);

        $this->assertEquals($this->wrapper, $result->getSource());
        $this->assertEquals($targetWrapper, $result->getTarget());
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
