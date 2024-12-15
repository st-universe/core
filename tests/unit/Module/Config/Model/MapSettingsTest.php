<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\StuTestCase;

class MapSettingsTest extends StuTestCase
{
    /** @var MockInterface&SettingsCoreInterface */
    private $settingsCore;
    /** @var MockInterface&SettingsCacheInterface */
    private $cache;

    private MapSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new MapSettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetEncryptionKeyExpectNullWhenNotPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('encryptionKey')
            ->andReturn(false);

        $key = $this->subject->getEncryptionKey();

        $this->assertNull($key);
    }

    public function testGetEncryptionKeyExpectStringWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('encryptionKey')
            ->andReturn(true);

        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('encryptionKey')
            ->andReturn("KEY");

        $key = $this->subject->getEncryptionKey();

        $this->assertEquals("KEY", $key);
    }
}
