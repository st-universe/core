<?php

namespace Stu\Module\Config\Model;

use Override;
final class AdminSettings extends AbstractSettings implements AdminSettingsInterface
{
    private const string CONFIG_PATH = 'admin';

    private const string SETTING_ID = 'id';
    private const string SETTING_EMAIL = 'email';

    #[Override]
    public function getId(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_ID);
    }

    #[Override]
    public function getEmail(): string
    {
        return $this->getStringConfigValue(self::SETTING_EMAIL);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
