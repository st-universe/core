<?php

namespace Stu\Module\Config;

use Stu\Module\Config\Model\GameSettingsInterface;

interface StuConfigInterface
{
    public function getGameSettings(): GameSettingsInterface;
}
