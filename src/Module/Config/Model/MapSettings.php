<?php

namespace Stu\Module\Config\Model;

use Override;
final class MapSettings extends AbstractSettings implements MapSettingsInterface
{
    private const string CONFIG_PATH = 'map';
    private const string SETTING_ENCRYPTION_KEY = 'encryptionKey';

    #[Override]
    public function getEncryptionKey(): ?string
    {
        if ($this->exists(self::SETTING_ENCRYPTION_KEY)) {
            return $this->getStringConfigValue(self::SETTING_ENCRYPTION_KEY);
        }

        return null;
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
