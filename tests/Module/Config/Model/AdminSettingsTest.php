<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\StuTestCase;

class AdminSettingsTest extends StuTestCase
{
    /** @var MockInterface|SettingsCoreInterface */
    private $settingsCore;
    /** @var MockInterface|SettingsCacheInterface */
    private $cache;

    private AdminSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new AdminSettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetId(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('id')
            ->once()
            ->andReturn(42);

        $result = $this->subject->getId();

        $this->assertEquals(42, $result);
    }


    public function testGetEmail(): void
    {
        $this->settingsCore->shouldReceive('getStringConfigValue')
            ->with('email')
            ->once()
            ->andReturn('foo@bar.de');

        $result = $this->subject->getEmail();

        $this->assertEquals('foo@bar.de', $result);
    }
}
