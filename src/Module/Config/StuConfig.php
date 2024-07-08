<?php

namespace Stu\Module\Config;

use Override;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\Model\DebugSettingsInterface;
use Stu\Module\Config\Model\GameSettingsInterface;
use Stu\Module\Config\Model\ResetSettingsInterface;
use Stu\Module\Config\Model\SettingsCacheInterface;

final class StuConfig implements StuConfigInterface
{
    public function __construct(
        private SettingsCacheInterface $settingsCache
    ) {
    }

    #[Override]
    public function getCacheSettings(): CacheSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::CACHE, null);
    }

    #[Override]
    public function getDbSettings(): DbSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::DB, null);
    }

    #[Override]
    public function getDebugSettings(): DebugSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::DEBUG, null);
    }

    #[Override]
    public function getGameSettings(): GameSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::GAME, null);
    }

    #[Override]
    public function getResetSettings(): ResetSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::RESET, null);
    }
}
