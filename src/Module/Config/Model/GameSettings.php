<?php

namespace Stu\Module\Config\Model;

use Override;
use Stu\Module\Config\StuConfigException;

final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const string CONFIG_PATH = 'game';

    private const string SETTING_ADMINS = 'admins';
    private const string SETTING_TEMP_DIR = 'temp_dir';
    private const string SETTING_USE_SEMAPHORES = 'useSemaphores';
    private const string SETTING_VERSION = 'version';
    private const string SETTING_WEBROOT = 'webroot';
    private const string SETTING_PIRATE_LOGFILE_PATH = 'pirate_logfile_path';

    #[Override]
    public function getAdminIds(): array
    {
        return array_map('intval', $this->getArrayConfigValue(self::SETTING_ADMINS, []));
    }

    #[Override]
    public function getAdminSettings(): AdminSettingsInterface
    {
        return new AdminSettings($this->getPath(), $this->getConfig());
    }

    #[Override]
    public function getColonySettings(): ColonySettingsInterface
    {
        return new ColonySettings($this->getPath(), $this->getConfig());
    }

    #[Override]
    public function getMapSettings(): MapSettingsInterface
    {
        return new MapSettings($this->getPath(), $this->getConfig());
    }

    #[Override]
    public function getTempDir(): string
    {
        return $this->getStringConfigValue(self::SETTING_TEMP_DIR);
    }

    #[Override]
    public function useSemaphores(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_SEMAPHORES, false);
    }

    #[Override]
    public function getVersion(): string|int
    {
        try {
            return $this->getIntegerConfigValue(self::SETTING_VERSION);
        } catch (StuConfigException) {
            return $this->getStringConfigValue(self::SETTING_VERSION);
        }
    }

    #[Override]
    public function getWebroot(): string
    {
        return $this->getStringConfigValue(self::SETTING_WEBROOT);
    }

    #[Override]
    public function getPirateLogfilePath(): string
    {
        return $this->getStringConfigValue(self::SETTING_PIRATE_LOGFILE_PATH);
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
