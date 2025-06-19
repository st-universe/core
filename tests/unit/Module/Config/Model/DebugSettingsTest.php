<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\StuTestCase;

class DebugSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private DebugSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new DebugSettings(null, $this->settingsCore, $this->cache);
    }

    public function testIsDebugMode(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('debug_mode', true)
            ->once()
            ->andReturn(true);

        $isDebugMode = $this->subject->isDebugMode();

        $this->assertTrue($isDebugMode);
    }

    public function testGetLogfilePath(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('logfile_path')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getLogfilePath();

        $this->assertEquals('/foo/bar', $path);
    }


    public function testGetLoglevel(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('loglevel')
            ->once()
            ->andReturn(42);

        $level = $this->subject->getLoglevel();

        $this->assertEquals(42, $level);
    }
}
