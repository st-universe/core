<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Lock;

use Mockery\MockInterface;
use org\bovigo\vfs\vfsStream;
use Override;
use Stu\Module\Config\StuConfigInterface;
use Stu\StuTestCase;

class LockManagerTest extends StuTestCase
{
    private MockInterface&StuConfigInterface $config;

    private LockManagerInterface $lockManager;

    #[Override]
    protected function setUp(): void
    {
        vfsStream::setup('tmpDir');
        $this->config = $this->mock(StuConfigInterface::class);

        $this->lockManager = new LockManager(
            $this->config
        );
    }

    public function testFunctionality(): void
    {
        $lockType = LockTypeEnum::COLONY_GROUP;

        $this->config->shouldReceive('getGameSettings->getColonySettings->getTickWorker')
            ->with()
            ->andReturn(3);

        $this->config->shouldReceive('getGameSettings->getTempDir')
            ->with()
            ->andReturn(vfsStream::url('tmpDir'));

        $this->assertFalse($this->lockManager->isLocked(42, $lockType));

        $this->lockManager->setLock(1, $lockType);
        $this->assertTrue($this->lockManager->isLocked(42, $lockType));
        $this->assertFalse($this->lockManager->isLocked(41, $lockType));
        $this->assertFalse($this->lockManager->isLocked(40, $lockType));
        $this->assertTrue($this->lockManager->isLocked(39, $lockType));
        $this->assertFalse($this->lockManager->isLocked(38, $lockType));
        $this->assertFalse($this->lockManager->isLocked(37, $lockType));

        $this->lockManager->clearLock(1, $lockType);
        $this->assertFalse($this->lockManager->isLocked(42, $lockType));
    }
}
