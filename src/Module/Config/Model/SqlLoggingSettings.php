<?php

namespace Stu\Module\Config\Model;

final class SqlLoggingSettings extends AbstractSettings implements SqlLoggingSettingsInterface
{
    private const CONFIG_PATH = 'sqlLogging';

    private const SETTING_IS_ACTIVE = 'isActive';
    private const SETTING_LOG_DIRECTORY = 'logDirectory';

    public function isActive(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_IS_ACTIVE, false);
    }

    public function getLogDirectory(): string
    {
        return $this->getStringConfigValue(self::SETTING_LOG_DIRECTORY);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
