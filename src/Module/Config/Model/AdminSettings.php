<?php

namespace Stu\Module\Config\Model;

use Override;

final class AdminSettings extends AbstractSettings implements AdminSettingsInterface
{
    private const string SETTING_ID = 'id';
    private const string SETTING_EMAIL = 'email';

    #[Override]
    public function getId(): int
    {
        return $this->settingsCore->getIntegerConfigValue(self::SETTING_ID);
    }

    #[Override]
    public function getEmail(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_EMAIL);
    }
}
