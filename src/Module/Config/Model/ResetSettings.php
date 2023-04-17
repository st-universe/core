<?php

namespace Stu\Module\Config\Model;

final class ResetSettings extends AbstractSettings implements ResetSettingsInterface
{
    private const CONFIG_PATH = 'reset';

    private const SETTING_DELAY_IN_SECONDS = 'delay_in_seconds';

    public function getDelayInSeconds(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_DELAY_IN_SECONDS, 5);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
