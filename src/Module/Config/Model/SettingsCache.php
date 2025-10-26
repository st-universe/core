<?php

namespace Stu\Module\Config\Model;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Config\StuConfigSettingEnum;

class SettingsCache implements SettingsCacheInterface
{
    /** @var Collection<string, SettingsInterface> */
    private Collection $settings;

    public function __construct(private SettingsFactoryInterface $settingsFactory)
    {
        $this->settings = new ArrayCollection();
    }

    #[\Override]
    public function getSettings(string $configInterface, ?SettingsInterface $parent): SettingsInterface
    {
        $configSetting = StuConfigSettingEnum::from($configInterface);

        $setting = $this->settings->get($configSetting->value);
        if ($setting === null) {

            $setting = $this->settingsFactory->createSettings($configSetting, $parent, $this);

            $this->settings->set(
                $configSetting->value,
                $setting
            );
        }

        return $setting;
    }
}
