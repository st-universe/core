<?php

namespace Stu\Module\Config\Model;

use Override;
final class DebugSettings extends AbstractSettings implements DebugSettingsInterface
{
    private const string CONFIG_PATH = 'debug';

    private const string SETTING_DEBUG_MODE = 'debug_mode';
    private const string SETTING_LOGFILE_PATH = 'logfile_path';
    private const string SETTING_LOGLEVEL = 'loglevel';

    #[Override]
    public function isDebugMode(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_DEBUG_MODE, true);
    }

    #[Override]
    public function getLogfilePath(): string
    {
        return $this->getStringConfigValue(self::SETTING_LOGFILE_PATH);
    }

    #[Override]
    public function getLoglevel(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_LOGLEVEL);
    }

    #[Override]
    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface
    {
        return new SqlLoggingSettings($this->getPath(), $this->getConfig());
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
