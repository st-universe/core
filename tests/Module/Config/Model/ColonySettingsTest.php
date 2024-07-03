<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Override;
use Mockery\MockInterface;
use Noodlehaus\ConfigInterface;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class ColonySettingsTest extends StuTestCase
{
    /** @var MockInterface|ConfigInterface */
    private ConfigInterface $config;

    /** @var MockObject|ColonySettings */
    private ColonySettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->config = $this->mock(ConfigInterface::class);

        $this->subject = new ColonySettings('game', $this->config);
    }

    public function testGetTickWorkerExpectConfigValueWhenPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.colony.tick_worker')
            ->once()
            ->andReturn(5);

        $tickWorker = $this->subject->getTickWorker();

        $this->assertEquals(5, $tickWorker);
    }

    public function testGetTickWorkerExpectDefaultWhenNotPresent(): void
    {
        $this->config->shouldReceive('get')
            ->with('game.colony.tick_worker')
            ->once()
            ->andReturn(null);

        $tickWorker = $this->subject->getTickWorker();

        $this->assertEquals(ColonySettings::SETTING_TICK_WORKER_DEFAULT, $tickWorker);
    }

    public function testGetTickWorkerExpectErrorWhenValueTooLow(): void
    {
        static::expectExceptionMessage('Invalid value for "game.colony.tick_worker", should be greater than 0.');
        static::expectException(StuConfigException::class);

        $this->config->shouldReceive('get')
            ->with('game.colony.tick_worker')
            ->once()
            ->andReturn(0);

        $this->subject->getTickWorker();
    }
}
