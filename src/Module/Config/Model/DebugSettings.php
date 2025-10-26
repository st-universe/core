<?php

namespace Stu\Module\Config\Model;


final class DebugSettings extends AbstractSettings implements DebugSettingsInterface
{
    private const string SETTING_DEBUG_MODE = 'debug_mode';
    private const string SETTING_LOGLEVEL = 'loglevel';

    #[\Override]
    public function isDebugMode(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_DEBUG_MODE, true);
    }

    #[\Override]
    public function getLoglevel(): int
    {
        return $this->settingsCore->getIntegerConfigValue(self::SETTING_LOGLEVEL);
    }

    #[\Override]
    public function getLoggingSettings(): LoggingSettingsInterface
    {
        return $this->settingsCache->getSettings(LoggingSettingsInterface::class, $this);
    }

    #[\Override]
    public function getSqlLoggingSettings(): SqlLoggingSettingsInterface
    {
        return $this->settingsCache->getSettings(SqlLoggingSettingsInterface::class, $this);
    }
}
