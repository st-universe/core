<?php

namespace Stu\Module\Config\Model;


final class GameSettings extends AbstractSettings implements GameSettingsInterface
{
    private const CONFIG_PATH = 'game';

    public function getColonySettings(): ColonySettingsInterface
    {
        return new ColonySettings($this->getPath(), $this->getConfig());
    }

    function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
