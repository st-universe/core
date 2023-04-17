<?php

namespace Stu\Module\Config\Model;

final class AdminSettings extends AbstractSettings implements AdminSettingsInterface
{
    private const CONFIG_PATH = 'admin';

    private const SETTING_ID = 'id';
    private const SETTING_EMAIL = 'email';

    public function getId(): int
    {
        return $this->getIntegerConfigValue(self::SETTING_ID);
    }

    public function getEmail(): string
    {
        return $this->getStringConfigValue(self::SETTING_EMAIL);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
