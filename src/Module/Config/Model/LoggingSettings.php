<?php

namespace Stu\Module\Config\Model;


final class LoggingSettings extends AbstractSettings implements LoggingSettingsInterface
{
    private const string SETTING_LOG_DIRECTORY = 'log_dir';
    private const string SETTING_GAME_REQUEST_LOGGING_ADAPTER = 'game_request_logging_adapter';

    #[\Override]
    public function getLogDirectory(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_LOG_DIRECTORY);
    }

    #[\Override]
    public function getGameRequestLoggingAdapter(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_GAME_REQUEST_LOGGING_ADAPTER);
    }
}
