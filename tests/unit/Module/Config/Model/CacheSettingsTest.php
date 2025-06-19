<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\StuTestCase;

class CacheSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private CacheSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new CacheSettings(null, $this->settingsCore, $this->cache);
    }

    public function testUseRedis(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('useRedis', true)
            ->once()
            ->andReturn(true);

        $useRedis = $this->subject->useRedis();

        $this->assertTrue($useRedis);
    }


    public function testGetRedisSocketExpectConfigValueWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('redis_socket')
            ->andReturn(true);

        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('redis_socket')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getRedisSocket();

        $this->assertEquals('test', $namespace);
    }

    public function testGetRedisSocketExpectNullWhenNotPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('redis_socket')
            ->andReturn(false);

        $this->assertEquals('', $this->subject->getRedisSocket());
    }

    public function testGetRedisHost(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('redis_host')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getRedisHost();

        $this->assertEquals('test', $namespace);
    }

    public function testGetRedisPort(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('redis_port')
            ->once()
            ->andReturn(42);

        $namespace = $this->subject->getRedisPort();

        $this->assertEquals(42, $namespace);
    }
}
