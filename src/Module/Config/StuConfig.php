<?php

namespace Stu\Module\Config;

use Noodlehaus\ConfigInterface;
use Override;
use Stu\Module\Config\Model\CacheSettings;
use Stu\Module\Config\Model\CacheSettingsInterface;
use Stu\Module\Config\Model\DbSettings;
use Stu\Module\Config\Model\DbSettingsInterface;
use Stu\Module\Config\Model\DebugSettings;
use Stu\Module\Config\Model\DebugSettingsInterface;
use Stu\Module\Config\Model\GameSettings;
use Stu\Module\Config\Model\GameSettingsInterface;
use Stu\Module\Config\Model\ResetSettings;
use Stu\Module\Config\Model\ResetSettingsInterface;

final class StuConfig implements StuConfigInterface
{
    public function __construct(private ConfigInterface $config)
    {
    }

    #[Override]
    public function getCacheSettings(): CacheSettingsInterface
    {
        return new CacheSettings(null, $this->config);
    }

    #[Override]
    public function getDbSettings(): DbSettingsInterface
    {
        return new DbSettings(null, $this->config);
    }

    #[Override]
    public function getDebugSettings(): DebugSettingsInterface
    {
        return new DebugSettings(null, $this->config);
    }

    #[Override]
    public function getGameSettings(): GameSettingsInterface
    {
        return new GameSettings(null, $this->config);
    }

    #[Override]
    public function getResetSettings(): ResetSettingsInterface
    {
        return new ResetSettings(null, $this->config);
    }
}
