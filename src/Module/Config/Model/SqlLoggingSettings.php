<?php

namespace Stu\Module\Config\Model;

final class SqlLoggingSettings extends AbstractSettings implements SqlLoggingSettingsInterface
{
    private const string SETTING_IS_ACTIVE = 'isActive';

    #[\Override]
    public function isActive(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_IS_ACTIVE, false);
    }
}
