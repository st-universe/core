<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\StuTestCase;

class LoggingSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private LoggingSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new LoggingSettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetLogDirectoryExpectConfigValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('log_dir')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getLogDirectory();

        $this->assertEquals('/foo/bar', $path);
    }

    public function testGameRequestLoggingAdapterExpectConfigValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('game_request_logging_adapter')
            ->once()
            ->andReturn('adapter');

        $path = $this->subject->getGameRequestLoggingAdapter();

        $this->assertEquals('adapter', $path);
    }
}
