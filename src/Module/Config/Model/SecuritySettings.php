<?php

namespace Stu\Module\Config\Model;

class SecuritySettings extends AbstractSettings implements SecuritySettingsInterface
{
    private const string SETTING_MASTER_PASSWORD = 'masterPassword';

    #[\Override]
    public function getMasterPassword(): ?string
    {
        if ($this->settingsCore->exists(self::SETTING_MASTER_PASSWORD)) {
            return $this->settingsCore->getStringConfigValue(self::SETTING_MASTER_PASSWORD);
        }

        return null;
    }
}
