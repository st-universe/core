<?php

namespace Stu\Module\Config\Model;

use Override;
use Stu\Module\Config\StuConfigSettingEnum;

final class DebugSettings extends AbstractSettings implements DebugSettingsInterface
{
    private const string SETTING_DEBUG_MODE = 'debug_mode';
    private const string SETTING_LOGFILE_PATH = 'logfile_path';
    private const string SETTING_LOGLEVEL = 'loglevel';

    #[Override]
    public function isDebugMode(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_DEBUG_MODE, true);
    }

    #[Override]
    public function getLogfilePath(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_LOGFILE_PATH);
    }

    #[Override]
    public function getLoglevel(): int
    {
        return $this->settingsCore->getIntegerConfigValue(self::SETTING_LOGLEVEL);
    }

    #[Override]
    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface
    {
        return $this->settingsCache->getSettings(StuConfigSettingEnum::SQL_LOGGING, $this);
    }
}
