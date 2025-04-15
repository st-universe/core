<?php

namespace Stu\Module\Config\Model;

use Override;
use Noodlehaus\ConfigInterface;
use Stu\Module\Config\StuConfigSettingEnum;

class SettingsFactory implements SettingsFactoryInterface
{
    public function __construct(private ConfigInterface $config) {}

    #[Override]
    public function createSettings(
        StuConfigSettingEnum $type,
        ?SettingsInterface $parent,
        SettingsCacheInterface $settingsCache
    ): SettingsInterface {
        return match ($type) {
            StuConfigSettingEnum::ADMIN => new AdminSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::CACHE => new CacheSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::COLONY => new ColonySettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::DB => new DbSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::DEBUG => new DebugSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::GAME => new GameSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::MAP => new MapSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::RESET => new ResetSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::SQL_LOGGING => new SqlLoggingSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::EMAIL => new EmailSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            ),
            StuConfigSettingEnum::PIRATES => new PirateSettings(
                $parent,
                $this->createSettingsCore($type, $parent),
                $settingsCache
            )
        };
    }

    private function createSettingsCore(StuConfigSettingEnum $type, ?SettingsInterface $parent): SettingsCoreInterface
    {
        return new SettingsCore(
            $parent,
            $type->getConfigPath(),
            $this->config
        );
    }
}
