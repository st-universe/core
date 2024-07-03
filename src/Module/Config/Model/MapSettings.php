<?php

namespace Stu\Module\Config\Model;

final class MapSettings extends AbstractSettings implements MapSettingsInterface
{
    private const CONFIG_PATH = 'map';
    private const SETTING_ENCRYPTION_KEY = 'encryptionKey';

    public function getEncryptionKey(): ?string
    {
        if ($this->exists(self::SETTING_ENCRYPTION_KEY)) {
            return $this->getStringConfigValue(self::SETTING_ENCRYPTION_KEY);
        }

        return null;
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
