<?php

namespace Stu\Module\Config\Model;


final class MapSettings extends AbstractSettings implements MapSettingsInterface
{
    private const string SETTING_ENCRYPTION_KEY = 'encryptionKey';

    #[\Override]
    public function getEncryptionKey(): ?string
    {
        if ($this->settingsCore->exists(self::SETTING_ENCRYPTION_KEY)) {
            return $this->settingsCore->getStringConfigValue(self::SETTING_ENCRYPTION_KEY);
        }

        return null;
    }
}
