<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Exception\AccessViolationException;
use Stu\Exception\EntityLockedException;
use Stu\Exception\SpacecraftDoesNotExistException;
use Stu\Exception\UnallowedUplinkOperationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperFactoryInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftLoaderTest extends StuTestCase
{
    /** @var MockInterface&SpacecraftRepositoryInterface */
    private $spacecraftRepository;
    /** @var MockInterface&CrewAssignmentRepositoryInterface */
    private $crewAssignmentRepository;
    /** @var MockInterface&SemaphoreUtilInterface */
    private $semaphoreUtil;
    /** @var MockInterface&SpacecraftWrapperFactoryInterface */
    private $spacecraftWrapperFactory;
    /** @var MockInterface&LockManagerInterface */
    private $lockManager;
    /** @var MockInterface&GameControllerInterface */
    private $game;

    /** @var MockInterface&SpacecraftInterface */
    private $spacecraft;
    /** @var MockInterface&SpacecraftWrapperInterface */
    private $wrapper;

    private int $spacecraftId = 5;
    private int $userId = 42;

    private SpacecraftLoaderInterface $subject;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->spacecraft = $this->mock(SpacecraftInterface::class);
        $this->wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $this->spacecraftRepository = $this->mock(SpacecraftRepositoryInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->semaphoreUtil = $this->mock(SemaphoreUtilInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->spacecraftWrapperFactory = $this->mock(SpacecraftWrapperFactoryInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);

        $this->spacecraft->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->userId);
        $this->spacecraft->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->spacecraftId);

        $this->subject = new SpacecraftLoader(
            $this->spacecraftRepository,
            $this->crewAssignmentRepository,
            $this->semaphoreUtil,
            $this->game,
            $this->spacecraftWrapperFactory,
            $this->lockManager
        );
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

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn(null);

        $this->subject->getByIdAndUser($this->spacecraftId, $this->userId);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipBelongsToOtherUser(): void
    {
        $this->expectException(AccessViolationException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(false);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOperationUnallowedWithUplink(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->crewAssignmentRepository->shouldReceive('hasCrewmanOfUser')
            ->with($this->spacecraft, 999)
            ->once()
            ->andReturn(true);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfUplinkOffline(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

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

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, true);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOwnerOnVacation(): void
    {
        $this->expectException(UnallowedUplinkOperationException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

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
        $this->spacecraft->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->subject->getByIdAndUser($this->spacecraftId, 999, true);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetByIdAndUserSuccessful(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->game->shouldReceive('isSemaphoreAlreadyAcquired')
            ->with($this->userId)
            ->once()
            ->andReturn(false);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->spacecraft);
        $this->verifyMainSemaphore();

        $result = $this->subject->getByIdAndUser($this->spacecraftId, $this->userId);

        $this->assertEquals($this->spacecraft, $result);
    }

    public function testGetWrapperByIdAndUserSuccessful(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->game->shouldReceive('isSemaphoreAlreadyAcquired')
            ->with($this->userId)
            ->once()
            ->andReturn(false);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        $result = $this->subject->getWrapperByIdAndUser($this->spacecraftId, $this->userId);

        $this->assertEquals($this->wrapper, $result);
    }

    public function testgetWrappersBySourceAndUserAndTargetAwaitTargetNull(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        //ship
        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        //target
        $this->spacecraftRepository->shouldReceive('find')
            ->with(0)
            ->once()
            ->andReturn(null);

        $result = $this->subject->getWrappersBySourceAndUserAndTarget($this->spacecraftId, $this->userId, 0);

        $this->assertNull($result->getTarget());
    }

    public function testgetWrappersBySourceAndUserAndTargetAwaitTargetWrapperIsNotNull(): void
    {
        $userSema = 123456;
        $targetUserSema = 23456;
        $target = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->spacecraftId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        //ship
        $this->spacecraftRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->spacecraft);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->spacecraftWrapperFactory->shouldReceive('wrapSpacecraft')
            ->with($this->spacecraft)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        //target
        $this->spacecraftRepository->shouldReceive('find')
            ->with(1)
            ->once()
            ->andReturn($target);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with(999)
            ->once()
            ->andReturn($targetUserSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with(999, $targetUserSema)
            ->once();
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

    private function verifyMainSemaphore(): void
    {
        $mainSema = 666;

        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY)
            ->once()
            ->andReturn($mainSema);
        $this->semaphoreUtil->shouldReceive('acquireMainSemaphore')
            ->with($mainSema)
            ->once();
        $this->semaphoreUtil->shouldReceive('releaseSemaphore')
            ->with($mainSema)
            ->once();
    }
}
