<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Stu\StuTestCase;

class DbSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private DbSettings $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new DbSettings(null, $this->settingsCore, $this->cache);
    }

    public function testUseSqlite(): void
    {
        $this->settingsCore->shouldReceive('getBooleanConfigValue')
            ->with('useSqlite', false)
            ->once()
            ->andReturn(true);

        $useSqlite = $this->subject->useSqlite();

        $this->assertTrue($useSqlite);
    }

    public function testGetSqliteDsn(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('sqliteDsn')
            ->once()
            ->andReturn('DSN');

        $result = $this->subject->getSqliteDsn();

        $this->assertEquals('DSN', $result);
    }

    public function testGetDatabase(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('database')
            ->once()
            ->andReturn('test');

        $namespace = $this->subject->getDatabase();

        $this->assertEquals('test', $namespace);
    }
}
