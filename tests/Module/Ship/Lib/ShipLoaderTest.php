<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Mockery\MockInterface;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Exception\AccessViolation;
use Stu\Exception\ShipDoesNotExistException;
use Stu\Exception\ShipIsDestroyedException;
use Stu\Exception\UnallowedUplinkOperation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;
use Stu\StuTestCase;

class ShipLoaderTest extends StuTestCase
{
    private ShipRepositoryInterface $shipRepository;

    private SemaphoreUtilInterface $semaphoreUtil;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private GameControllerInterface $game;

    private ShipLoader $shipLoader;

    /**
     * @var MockInterface|ShipInterface
     */
    private $ship;

    /**
     * @var MockInterface|ShipWrapperInterface
     */
    private $wrapper;

    private int $shipId = 5;
    private int $userId = 42;

    public function setUp(): void
    {
        //injected
        $this->ship = $this->mock(ShipInterface::class);
        $this->wrapper = $this->mock(ShipWrapperInterface::class);
        $this->shipRepository = $this->mock(ShipRepositoryInterface::class);
        $this->semaphoreUtil = $this->mock(SemaphoreUtilInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);
        $this->shipWrapperFactory = $this->mock(ShipWrapperFactoryInterface::class);

        $loggerUtil = $this->mock(LoggerUtilInterface::class);
        $loggerUtilFactory = $this->mock(LoggerUtilFactoryInterface::class);

        $loggerUtilFactory->shouldReceive('getLoggerUtil')
            ->withNoArgs()
            ->once()
            ->andReturn($loggerUtil);
        $loggerUtil->shouldReceive('log')
            ->withSomeOfArgs()
            ->zeroOrMoreTimes();

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
            $loggerUtilFactory
        );
    }

    public function testByIdAndUserAwaitExceptionIfShipNonExistent(): void
    {
        $this->expectException(ShipDoesNotExistException::class);

        $this->shipRepository->shouldReceive('find')
            ->with(5)
            ->once()
            ->andReturn(null);

        $this->shipLoader->getByIdAndUser($this->shipId, $this->userId);
    }

    public function testByIdAndUserAwaitExceptionIfShipIsDestroyed(): void
    {
        $this->expectException(ShipIsDestroyedException::class);
        $userSema = 123456;

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

    public function testByIdAndUserAwaitExceptionIfShipBelongsToOtherUser(): void
    {
        $this->expectException(AccessViolation::class);

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

    public function testByIdAndUserAwaitExceptionIfOperationUnallowedWithUplink(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

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

    public function testByIdAndUserAwaitExceptionIfUplinkOffline(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

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

    public function testByIdAndUserAwaitExceptionIfOwnerOnVacation(): void
    {
        $this->expectException(UnallowedUplinkOperation::class);

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

    public function testGetWrappersByIdAndUserAndTargetAwaitTargetNull(): void
    {
        $userSema = 123456;

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

        $resultArray = $this->shipLoader->getWrappersByIdAndUserAndTarget($this->shipId, $this->userId, 0);

        $this->assertEquals(2, count($resultArray));
        $this->assertNull($resultArray[0]);
    }

    public function testGetWrappersByIdAndUserAndTargetAwaitTargetWrapperIsNotNull(): void
    {
        $userSema = 123456;
        $targetUserSema = 23456;
        $target = $this->mock(ShipInterface::class);
        $targetWrapper = $this->mock(ShipWrapperInterface::class);

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

        $resultArray = $this->shipLoader->getWrappersByIdAndUserAndTarget($this->shipId, $this->userId, 1);

        $this->assertEquals(2, count($resultArray));
        $this->assertEquals($targetWrapper, $resultArray[1]);
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
