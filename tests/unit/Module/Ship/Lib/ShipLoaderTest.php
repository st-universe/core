<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Override;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Exception\EntityLockedException;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipLoaderTest extends StuTestCase
{
    /** @var MockInterface|ShipRepositoryInterface */
    private  $shipRepository;
    /** @var MockInterface|SemaphoreUtilInterface */
    private  $semaphoreUtil;
    /** @var MockInterface|ShipWrapperFactoryInterface */
    private  $shipWrapperFactory;
    /** @var MockInterface|LockManagerInterface */
    private  $lockManager;
    /** @var MockInterface|GameControllerInterface */
    private  $game;

    private  ShipLoader $shipLoader;

    /** @var MockInterface|ShipInterface */
    private $ship;

    /**
     * @var MockInterface|ShipWrapperInterface
     */
    private $wrapper;

    private int $shipId = 5;
    private int $userId = 42;

    #[Override]
    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->semaphoreUtil = $this->mock(SemaphoreUtilInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);

        $this->ship->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->userId);
        $this->ship->shouldReceive('getId')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($this->shipId);

        $this->shipLoader = new ShipLoader(
            $this->shipRepository,
            $this->semaphoreUtil,
            $this->game,
            $this->shipWrapperFactory,
            $this->lockManager
        );
    }

    public function testgGtByIdAndUserExpectErrorWhenEntityLocked(): void
    {
        static::expectExceptionMessage('Tick läuft gerade, Zugriff auf Schiff ist daher blockiert');
        static::expectException(EntityLockedException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(true);

        $this->shipLoader->getByIdAndUser($this->shipId, $this->userId);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipNonExistent(): void
    {
        $this->expectException(ShipDoesNotExistException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn(null);

        $this->shipLoader->getByIdAndUser($this->shipId, $this->userId);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipIsDestroyed(): void
    {
        $this->expectException(ShipIsDestroyedException::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->shipLoader->getByIdAndUser($this->shipId, $this->userId);
    }

    public function testGetByIdAndUserAwaitExceptionIfShipBelongsToOtherUser(): void
    {
        $this->expectException(AccessViolation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('hasCrewmanOfUser')
            ->with(999)
            ->once()
            ->andReturn(false);

        $result = $this->shipLoader->getByIdAndUser($this->shipId, 999);

        $this->assertEquals($this->ship, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOperationUnallowedWithUplink(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('hasCrewmanOfUser')
            ->with(999)
            ->once()
            ->andReturn(true);

        $result = $this->shipLoader->getByIdAndUser($this->shipId, 999);

        $this->assertEquals($this->ship, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfUplinkOffline(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('hasCrewmanOfUser')
            ->with(999)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystemState')
            ->with(ShipSystemTypeEnum::SYSTEM_UPLINK)
            ->once()
            ->andReturn(false);

        $result = $this->shipLoader->getByIdAndUser($this->shipId, 999, true);

        $this->assertEquals($this->ship, $result);
    }

    public function testGetByIdAndUserAwaitExceptionIfOwnerOnVacation(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->ship->shouldReceive('hasCrewmanOfUser')
            ->with(999)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getSystemState')
            ->with(ShipSystemTypeEnum::SYSTEM_UPLINK)
            ->once()
            ->andReturn(true);
        $this->ship->shouldReceive('getUser->isVacationRequestOldEnough')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $result = $this->shipLoader->getByIdAndUser($this->shipId, 999, true);

        $this->assertEquals($this->ship, $result);
    }

    public function testGetByIdAndUserSuccessful(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
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
        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($this->ship)
            ->once()
            ->andReturn($this->wrapper);
        $this->wrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($this->ship);
        $this->verifyMainSemaphore();

        $result = $this->shipLoader->getByIdAndUser($this->shipId, $this->userId);

        $this->assertEquals($this->ship, $result);
    }

    public function testGetWrapperByIdAndUserSuccessful(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
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
        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($this->ship)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        $result = $this->shipLoader->getWrapperByIdAndUser($this->shipId, $this->userId);

        $this->assertEquals($this->wrapper, $result);
    }

    public function testgetWrappersBySourceAndUserAndTargetAwaitTargetNull(): void
    {
        $userSema = 123456;

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        //ship
        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($this->ship)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        //target
        $this->shipRepository->shouldReceive('find')
            ->with(0)
            ->once()
            ->andReturn(null);

        $result = $this->shipLoader->getWrappersBySourceAndUserAndTarget($this->shipId, $this->userId, 0);

        $this->assertNull($result->getTarget());
    }

    public function testgetWrappersBySourceAndUserAndTargetAwaitTargetWrapperIsNotNull(): void
    {
        $userSema = 123456;
        $targetUserSema = 23456;
        $target = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);

        $this->lockManager->shouldReceive('isLocked')
            ->with($this->shipId, LockTypeEnum::SHIP_GROUP)
            ->once()
            ->andReturn(false);

        //ship
        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn($this->ship);
        $this->ship->shouldReceive('isDestroyed')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->semaphoreUtil->shouldReceive('getSemaphore')
            ->with($this->userId)
            ->once()
            ->andReturn($userSema);
        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with($this->userId, $userSema)
            ->once();
        $this->shipWrapperFactory->shouldReceive('wrapShip')
            ->with($this->ship)
            ->once()
            ->andReturn($this->wrapper);
        $this->verifyMainSemaphore();

        //target
        $this->shipRepository->shouldReceive('find')
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
        $this->shipWrapperFactory->shouldReceive('wrapShip')
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

        $result = $this->shipLoader->getWrappersBySourceAndUserAndTarget($this->shipId, $this->userId, 1);

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
