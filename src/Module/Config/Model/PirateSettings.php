<?php

namespace Stu\Module\Config\Model;

use Override;

class PirateSettings extends AbstractSettings implements PirateSettingsInterface
{
    private const string SETTING_DO_PIRATE_TICK = 'doPirateTick';
    private const string SETTING_PIRATE_LOGFILE_PATH = 'logfilePath';

    #[Override]
    public function isPirateTickActive(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_DO_PIRATE_TICK);
    }

    #[Override]
    public function getPirateLogfilePath(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_PIRATE_LOGFILE_PATH);
    }
}
