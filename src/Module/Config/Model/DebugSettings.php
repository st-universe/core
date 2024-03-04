<?php

namespace Stu\Module\Config\Model;

final class DebugSettings extends AbstractSettings implements DebugSettingsInterface
{
    private const CONFIG_PATH = 'debug';

    private const SETTING_DEBUG_MODE = 'debug_mode';
    private const SETTING_LOGFILE_PATH = 'logfile_path';
    private const SETTING_LOGLEVEL = 'loglevel';

    public function isDebugMode(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_DEBUG_MODE, true);
    }

    public function getLogfilePath(): string
    {
        return $this->getStringConfigValue(self::SETTING_LOGFILE_PATH);
    }

    public function getLoglevel(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_LOGLEVEL);
    }

    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface
    {
        return new SqlLoggingSettings($this->getPath(), $this->getConfig());
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
