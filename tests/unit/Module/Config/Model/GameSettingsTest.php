<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\Module\Config\StuConfigException;
use Stu\StuTestCase;

class GameSettingsTest extends StuTestCase
{
    /** @var MockInterface|SettingsCoreInterface */
    private $settingsCore;
    /** @var MockInterface|SettingsCacheInterface */
    private $cache;

    private GameSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new GameSettings(null, $this->settingsCore, $this->cache);
    }

    public function testgetAdminIds(): void
    {
        $this->settingsCore->shouldReceive('getArrayConfigValue')
            ->with('admins', [])
            ->once()
            ->andReturn(['5', '42']);

        $admins = $this->subject->getAdminIds();

        $this->assertEquals([5, 42], $admins);
    }

    public function testUseSemaphores(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('useSemaphores', false)
            ->once()
            ->andReturn(true);

        $result = $this->subject->useSemaphores();

        $this->assertTrue($result);
    }


    public function testGetTempDir(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('temp_dir')
            ->once()
            ->andReturn('folder');

        $tempDir = $this->subject->getTempDir();

        $this->assertEquals('folder', $tempDir);
    }

    public function testGetVersionExpectIntegerValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('version')
            ->once()
            ->andReturn(1234);

        $version = $this->subject->getVersion();

        $this->assertEquals(1234, $version);
    }

    public function testGetVersionExpectStringValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('version')
            ->once()
            ->andThrow(StuConfigException::class);

        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('version')
            ->once()
            ->andReturn('1234');

        $version = $this->subject->getVersion();

        $this->assertEquals('1234', $version);
    }

    public function testGetWebrootExpectConfigValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('webroot')
            ->once()
            ->andReturn('path');

        $webroot = $this->subject->getWebroot();

        $this->assertEquals('path', $webroot);
    }

    public function testGetPirateLogfilePath(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('pirate_logfile_path')
            ->once()
            ->andReturn('/foo/bar');

        $path = $this->subject->getPirateLogfilePath();

        $this->assertEquals('/foo/bar', $path);
    }
}
