<?php

namespace Stu\Module\Config\Model;

use Override;
use Stu\Module\Config\StuConfigException;
use Stu\Module\Config\StuConfigSettingEnum;

final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const string SETTING_ADMINS = 'admins';
    private const string SETTING_TEMP_DIR = 'temp_dir';
    private const string SETTING_USE_SEMAPHORES = 'useSemaphores';
    private const string SETTING_VERSION = 'version';
    private const string SETTING_WEBROOT = 'webroot';
    private const string SETTING_PIRATE_LOGFILE_PATH = 'pirate_logfile_path';

    #[Override]
    public function getAdminIds(): array
    {
        return array_map('intval', $this->settingsCore->getArrayConfigValue(self::SETTING_ADMINS, []));
    }

    #[Override]
    public function getAdminSettings(): AdminSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::ADMIN, $this);
    }

    #[Override]
    public function getColonySettings(): ColonySettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::COLONY, $this);
    }

    #[Override]
    public function getEmailSettings(): EmailSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::EMAIL, $this);
    }

    #[Override]
    public function getMapSettings(): MapSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::MAP, $this);
    }

    #[Override]
    public function getTempDir(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_TEMP_DIR);
    }

    #[Override]
    public function useSemaphores(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_USE_SEMAPHORES, false);
    }

    #[Override]
    public function getVersion(): string|int
    {
        try {
            return $this->settingsCore->getIntegerConfigValue(self::SETTING_VERSION);
        } catch (StuConfigException) {
            return $this->settingsCore->getStringConfigValue(self::SETTING_VERSION);
        }
    }

    #[Override]
    public function getWebroot(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_WEBROOT);
    }

    #[Override]
    public function getPirateLogfilePath(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_PIRATE_LOGFILE_PATH);
    }
}
