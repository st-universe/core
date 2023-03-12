<?php

namespace Stu\Module\Config;

use Noodlehaus\ConfigInterface;
use Stu\Module\Config\Model\CacheSettings;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\DbSettings;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\Model\DebugSettings;
use Stu\Module\Config\Model\DebugSettingsInterface;
use Stu\Module\Config\Model\GameSettings;
use Stu\Module\Config\Model\GameSettingsInterface;

final class StuConfig implements StuConfigInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function getCacheSettings(): CacheSettingsInterface
    {
        return new CacheSettings(null, $this->config);
    }

    public function getDbSettings(): DbSettingsInterface
    {
        return new DbSettings(null, $this->config);
    }

    public function getDebugSettings(): DebugSettingsInterface
    {
        return new DebugSettings(null, $this->config);
    }

    public function getGameSettings(): GameSettingsInterface
    {
        return new GameSettings(null, $this->config);
    }
}
