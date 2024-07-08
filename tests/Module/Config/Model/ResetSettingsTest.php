<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\MockObject\MockObject;
use Stu\StuTestCase;

class ResetSettingsTest extends StuTestCase
{
    /** @var MockInterface|SettingsCoreInterface */
    private $settingsCore;
    /** @var MockInterface|SettingsCacheInterface */
    private $cache;

    /** @var MockObject|ResetSettings */
    private ResetSettings $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsCore = $this->mock(SettingsCoreInterface::class);
        $this->cache = $this->mock(SettingsCacheInterface::class);

        $this->subject = new ResetSettings(null, $this->settingsCore, $this->cache);
    }

    public function testGetDelayInSeconds(): void
    {
        $this->settingsCore->shouldReceive('getIntegerConfigValue')
            ->with('delay_in_seconds', 5)
            ->once()
            ->andReturn(15);

        $result = $this->subject->getDelayInSeconds();

        $this->assertEquals(15, $result);
    }
}
