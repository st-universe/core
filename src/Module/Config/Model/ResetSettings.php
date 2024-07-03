<?php

namespace Stu\Module\Config\Model;

use Override;
final class ResetSettings extends AbstractSettings implements ResetSettingsInterface
{
    private const string CONFIG_PATH = 'reset';

    private const string SETTING_DELAY_IN_SECONDS = 'delay_in_seconds';

    #[Override]
    public function getDelayInSeconds(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_DELAY_IN_SECONDS, 5);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
