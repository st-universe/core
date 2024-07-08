<?php

namespace Stu\Module\Config\Model;

use Override;

final class ResetSettings extends AbstractSettings implements ResetSettingsInterface
{
    private const string SETTING_DELAY_IN_SECONDS = 'delay_in_seconds';

    #[Override]
    public function getDelayInSeconds(): int
    {
        return $this->settingsCore->getIntegerConfigValue(self::SETTING_DELAY_IN_SECONDS, 5);
    }
}
