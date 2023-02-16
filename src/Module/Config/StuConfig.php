<?php

namespace Stu\Module\Config;

use Noodlehaus\ConfigInterface;
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

    public function getGameSettings(): GameSettingsInterface
    {
        return new GameSettings(null, $this->config);
    }
}
