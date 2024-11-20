<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\StuTestCase;

class SqlLoggingSettingsTest extends StuTestCase
{
    /** @var MockInterface|SettingsCoreInterface */
    private $settingsCore;
    /** @var MockInterface|SettingsCacheInterface */
    private $cache;

    private SqlLoggingSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new SqlLoggingSettings(null, $this->settingsCore, $this->cache);
    }

    public function testIsActive(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('isActive', false)
            ->once()
            ->andReturn(true);

        $result = $this->subject->isActive();

        $this->assertTrue($result);
    }

    public function testGetLogDirectoryExpectConfigValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('logDirectory')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getLogDirectory();

        $this->assertEquals('/foo/bar', $path);
    }
}
