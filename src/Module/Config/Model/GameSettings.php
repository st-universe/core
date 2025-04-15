<?php

namespace Stu\Module\Config\Model;

use Override;
use Stu\Module\Config\StuConfigException;

final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const string SETTING_ADMINS = 'admins';
    private const string SETTING_GRANTED_FEATURES = 'grantedFeatures';
    private const string SETTING_TEMP_DIR = 'temp_dir';
    private const string SETTING_USE_SEMAPHORES = 'useSemaphores';
    private const string SETTING_VERSION = 'version';
    private const string SETTING_WEBROOT = 'webroot';

    #[Override]
    public function getAdminIds(): array
    {
        return array_map('intval', $this->settingsCore->getArrayConfigValue(self::SETTING_ADMINS, []));
    }

    #[Override]
    public function getAdminSettings(): AdminSettingsInterface
    {
        return $this->settingsCache->getSettings(AdminSettingsInterface::class, $this);
    }

    #[Override]
    public function getColonySettings(): ColonySettingsInterface
    {
        return $this->settingsCache->getSettings(ColonySettingsInterface::class, $this);
    }

    #[Override]
    public function getEmailSettings(): EmailSettingsInterface
    {
        return $this->settingsCache->getSettings(EmailSettingsInterface::class, $this);
    }

    #[Override]
    public function getGrantedFeatures(): array
    {
        return $this->settingsCore->getArrayConfigValue(self::SETTING_GRANTED_FEATURES, []);
    }

    #[Override]
    public function getMapSettings(): MapSettingsInterface
    {
        return $this->settingsCache->getSettings(MapSettingsInterface::class, $this);
    }

    #[Override]
    public function getPirateSettings(): PirateSettingsInterface
    {
        return $this->settingsCache->getSettings(PirateSettingsInterface::class, $this);
    }

    #[Override]
    public function getTempDir(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_TEMP_DIR);
    }

    #[Override]
    public function useSemaphores(): bool
    {
        return $this->settingsCore->getBooleanConfigValue(self::SETTING_USE_SEMAPHORES, false);
    }

    #[Override]
    public function getVersion(): string|int
    {
        try {
            return $this->settingsCore->getIntegerConfigValue(self::SETTING_VERSION);
        } catch (StuConfigException) {
            return $this->settingsCore->getStringConfigValue(self::SETTING_VERSION);
        }
    }

    #[Override]
    public function getWebroot(): string
    {
        return $this->settingsCore->getStringConfigValue(self::SETTING_WEBROOT);
    }
}
