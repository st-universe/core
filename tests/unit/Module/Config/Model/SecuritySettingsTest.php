<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Stu\StuTestCase;

class SecuritySettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private SecuritySettingsInterface $subject;

    #[\Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new SecuritySettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetEncryptionKeyExpectNullWhenNotPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('masterPassword')
            ->andReturn(false);

        $key = $this->subject->getMasterPassword();

        $this->assertNull($key);
    }

    public function testGetEncryptionKeyExpectStringWhenPresent(): void
    {
        $this->settingsCore->shouldReceive('exists')
            ->with('masterPassword')
            ->andReturn(true);

        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('masterPassword')
            ->andReturn("PW!");

        $key = $this->subject->getMasterPassword();

        $this->assertEquals("PW!", $key);
    }
}
