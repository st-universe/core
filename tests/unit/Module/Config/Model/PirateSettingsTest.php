<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\StuTestCase;

class PirateSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private PirateSettingsInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new PirateSettings(null, $this->settingsCore, $this->cache);
    }

    public function testIsPirateTickActive(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('doPirateTick')
            ->once()
            ->andReturn(true);

        $result = $this->subject->isPirateTickActive();

        $this->assertTrue($result);
    }

    public function testGetPirateLogfilePath(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('logfilePath')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getPirateLogfilePath();

        $this->assertEquals('/foo/bar', $path);
    }
}
