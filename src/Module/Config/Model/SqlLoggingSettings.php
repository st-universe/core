<?php

namespace Stu\Module\Config\Model;

use Override;
final class SqlLoggingSettings extends AbstractSettings implements SqlLoggingSettingsInterface
{
    private const string CONFIG_PATH = 'sqlLogging';

    private const string SETTING_IS_ACTIVE = 'isActive';
    private const string SETTING_LOG_DIRECTORY = 'logDirectory';

    #[Override]
    public function isActive(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_IS_ACTIVE, false);
    }

    #[Override]
    public function getLogDirectory(): string
    {
        return $this->getStringConfigValue(self::SETTING_LOG_DIRECTORY);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
