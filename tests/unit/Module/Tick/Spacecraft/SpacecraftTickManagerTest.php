<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Spacecraft;

use Doctrine\ORM\EntityManagerInterface;
use Mockery\MockInterface;
use RuntimeException;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Spacecraft\ManagerComponent\ManagerComponentInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

class SpacecraftTickManagerTest extends StuTestCase
{
    private UserRepositoryInterface&MockInterface $userRepository;
    private LockManagerInterface&MockInterface $lockManager;
    private StuConfigInterface&MockInterface $config;
    private ManagerComponentInterface&MockInterface $managerComponent;

    private SpacecraftTickManager $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->userRepository = $this->mock(UserRepositoryInterface::class);
        $this->lockManager = $this->mock(LockManagerInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);
        $this->managerComponent = $this->mock(ManagerComponentInterface::class);

        $this->subject = new SpacecraftTickManager(
            $this->userRepository,
            $this->lockManager,
            $this->config,
            $this->mock(EntityManagerInterface::class),
            [$this->managerComponent]
        );
    }

    public function testWorkLocksAllUsersOnNonSqliteDatabase(): void
    {
        $dbSettings = $this->mock(DbSettingsInterface::class);

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
        $this->managerComponent->shouldReceive('work')
            ->withNoArgs()
            ->once();

        $this->subject->work();
    }

    public function testWorkClearsLockOnError(): void
    {
        $dbSettings = $this->mock(DbSettingsInterface::class);

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
            ->andReturn(true);
        $this->managerComponent->shouldReceive('work')
            ->withNoArgs()
            ->once()
            ->andThrow(new RuntimeException('component failed'));

        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('component failed');

        $this->subject->work();
    }
}
