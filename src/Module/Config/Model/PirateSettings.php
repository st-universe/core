<?php

namespace Stu\Module\Config\Model;


class PirateSettings extends AbstractSettings implements PirateSettingsInterface
{
    private const string SETTING_DO_PIRATE_TICK = 'doPirateTick';

    #[\Override]
    public function isPirateTickActive(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_DO_PIRATE_TICK);
    }
}
