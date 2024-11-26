<?php

declare(strict_types=1);

namespace Stu\Module\Config\Model;

use Mockery\MockInterface;
use Override;
use Stu\Module\Config\StuConfigSettingEnum;
use Stu\StuTestCase;

class SettingsCacheTest extends StuTestCase
{
    /** @var MockInterface|SettingsFactoryInterface */
    private $settingsFactory;

    private SettingsCacheInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->settingsFactory = $this->mock(SettingsFactoryInterface::class);

        $this->subject = new SettingsCache($this->settingsFactory);
    }

    public function testGetSettingsExpectSingleCreation(): void
    {
        $parent = $this->mock(SettingsInterface::class);
        $mapSettings = $this->mock(SettingsInterface::class);

        $this->settingsFactory->shouldReceive('createSettings')
            ->with(StuConfigSettingEnum::MAP, $parent, $this->subject)
            ->once()
            ->andReturn($mapSettings);

        $this->subject->getSettings(StuConfigSettingEnum::MAP, $parent);
        $result = $this->subject->getSettings(StuConfigSettingEnum::MAP, $parent);

        $this->assertSame($mapSettings, $result);
    }
}
