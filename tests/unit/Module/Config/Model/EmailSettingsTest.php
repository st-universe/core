<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\StuTestCase;

class EmailSettingsTest extends StuTestCase
{
    private MockInterface&SettingsCoreInterface $settingsCore;
    private MockInterface&SettingsCacheInterface $cache;

    private EmailSettingsInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new EmailSettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetTransportDsn(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('transportDsn')
            ->andReturn('TRANSPORT_DSN');

        $result = $this->subject->getTransportDsn();

        $this->assertEquals('TRANSPORT_DSN', $result);
    }

    public function testGetSenderAddress(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('senderAddress')
            ->andReturn('SEN@D.ER');

        $result = $this->subject->getSenderAddress();

        $this->assertEquals('SEN@D.ER', $result);
    }
}
