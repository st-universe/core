<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class ColonySettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private ColonySettings $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new ColonySettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetTickWorkerExpectConfigValueWhenValueGreaterZero(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('tick_worker', 1)
            ->once()
            ->andReturn(5);

        $tickWorker = $this->subject->getTickWorker();

        $this->assertEquals(5, $tickWorker);
    }

    public function testGetTickWorkerExpectErrorWhenValueTooLow(): void
    {
        static::expectExceptionMessage('Invalid value for "game.colony.tick_worker", should be greater than 0.');
        static::expectException(StuConfigException::class);

        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('tick_worker', 1)
            ->once()
            ->andReturn(0);
        $this->settingsCore->shouldReceive('getPath')
            ->withNoArgs()
            ->once()
            ->andReturn('game.colony');

        $this->subject->getTickWorker();
    }
}
