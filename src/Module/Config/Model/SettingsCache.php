<?php

namespace Stu\Module\Config\Model;

use Override;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Stu\Module\Config\StuConfigSettingEnum;

class SettingsCache implements SettingsCacheInterface
{
    /** @var Collection<int, SettingsInterface> */
    private Collection $settings;

    public function __construct(private SettingsFactoryInterface $settingsFactory)
    {
        $this->settings = new ArrayCollection();
    }

    #[Override]
    public function getSettings(StuConfigSettingEnum $type, ?SettingsInterface $parent): SettingsInterface
    {
        $setting = $this->settings->get($type->value);
        if ($setting === null) {

            $setting = $this->settingsFactory->createSettings($type, $parent, $this);

            $this->settings->set(
                $type->value,
                $setting
            );
        }

        return $setting;
    }
}
