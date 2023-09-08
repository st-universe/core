<?php

namespace Stu\Module\Config\Model;

use Stu\Module\Config\StuConfigException;

final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const CONFIG_PATH = 'game';

    private const SETTING_ADMINS = 'admins';
    private const SETTING_TEMP_DIR = 'temp_dir';
    private const SETTING_USE_SEMAPHORES = 'useSemaphores';
    private const SETTING_VERSION = 'version';
    private const SETTING_WEBROOT = 'webroot';

    public function getAdminIds(): array
    {
        return array_map('intval', $this->getArrayConfigValue(self::SETTING_ADMINS, []));
    }

    public function getAdminSettings(): AdminSettingsInterface
    {
        return new AdminSettings($this->getPath(), $this->getConfig());
    }

    public function getColonySettings(): ColonySettingsInterface
    {
        return new ColonySettings($this->getPath(), $this->getConfig());
    }

    public function getMapSettings(): MapSettingsInterface
    {
        return new MapSettings($this->getPath(), $this->getConfig());
    }

    public function getTempDir(): string
    {
        return $this->getStringConfigValue(self::SETTING_TEMP_DIR);
    }

    public function useSemaphores(): bool
    {
        return $this->getBooleanConfigValue(self::SETTING_USE_SEMAPHORES, false);
    }

    public function getVersion(): string|int
    {
        try {
            return $this->getIntegerConfigValue(self::SETTING_VERSION);
        } catch (StuConfigException $e) {
            return $this->getStringConfigValue(self::SETTING_VERSION);
        }
    }

    public function getWebroot(): string
    {
        return $this->getStringConfigValue(self::SETTING_WEBROOT);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
