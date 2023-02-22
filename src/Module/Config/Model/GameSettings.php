<?php

namespace Stu\Module\Config\Model;

final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const CONFIG_PATH = 'game';

    private const SETTING_TEMP_DIR = 'temp_dir';

    public function getColonySettings(): ColonySettingsInterface
    {
        return new ColonySettings($this->getPath(), $this->getConfig());
    }

    public function getTempDir(): string
    {
        return $this->getStringConfigValue(self::SETTING_TEMP_DIR);
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
