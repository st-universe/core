<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\SemaphoreUtilInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftTickManagerTest extends StuTestCase
{
    private SemaphoreUtilInterface&MockInterface $semaphoreUtil;
    private UserRepositoryInterface&MockInterface $userRepository;
    private LockManagerInterface&MockInterface $lockManager;
    private StuConfigInterface&MockInterface $config;
    private SpacecraftTickManager $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->semaphoreUtil = $this->mock(SemaphoreUtilInterface::class);
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);

        $this->subject = new SpacecraftTickManager(
            $this->semaphoreUtil,
            $this->userRepository,
            $this->lockManager,
            $this->config,
            $this->mock(EntityManagerInterface::class),
            []
        );
    }

    public function testWorkAcquiresMainSemaphoreAndLocksAllUsersOnNonSqliteDatabase(): void
    {
        $dbSettings = $this->mock(DbSettingsInterface::class);

        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY)
            ->once();
        $this->lockManager->shouldReceive('setLock')
            ->with(1, LockTypeEnum::SHIP_GROUP)
            ->once();
        $this->lockManager->shouldReceive('clearLock')
            ->with(1, LockTypeEnum::SHIP_GROUP)
            ->once();
        $this->config->shouldReceive('getDbSettings')
            ->withNoArgs()
            ->once()
            ->andReturn($dbSettings);
        $dbSettings->shouldReceive('useSqlite')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->userRepository->shouldReceive('lockAllUsersForUpdate')
            ->withNoArgs()
            ->once();

        $this->subject->work();
    }

    public function testWorkReleasesSemaphoreAndClearsLockWhenUserLockingFails(): void
    {
        $semaphore = 42;
        $dbSettings = $this->mock(DbSettingsInterface::class);

        $this->semaphoreUtil->shouldReceive('acquireSemaphore')
            ->with(SemaphoreConstants::MAIN_SHIP_SEMAPHORE_KEY)
            ->once()
            ->andReturn($semaphore);
        $this->semaphoreUtil->shouldReceive('releaseSemaphore')
            ->with($semaphore)
            ->once();
        $this->lockManager->shouldReceive('setLock')
            ->with(1, LockTypeEnum::SHIP_GROUP)
            ->once();
        $this->lockManager->shouldReceive('clearLock')
            ->with(1, LockTypeEnum::SHIP_GROUP)
            ->once();
        $this->config->shouldReceive('getDbSettings')
            ->withNoArgs()
            ->once()
            ->andReturn($dbSettings);
        $dbSettings->shouldReceive('useSqlite')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->userRepository->shouldReceive('lockAllUsersForUpdate')
            ->withNoArgs()
            ->once()
            ->andThrow(new RuntimeException('database lock failed'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('database lock failed');

        $this->subject->work();
    }
}
