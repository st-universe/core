<?php

namespace Stu\Module\Config\Model;

use Stu\Module\Config\StuConfigException;

final class ColonySettings extends AbstractSettings implements ColonySettingsInterface
{
    private const CONFIG_PATH = 'colony';

    private const SETTING_TICK_WORKER = 'tick_worker';

    public function getTickWorker(): int
    {
        $tickWorker = $this->getIntegerConfigValue(self::SETTING_TICK_WORKER, 1);

        if ($tickWorker < 1) {
            throw new StuConfigException(sprintf('Invalid value for %s.%s, should be greater than 0.', $this->getPath(), self::SETTING_TICK_WORKER));
        }

        return $tickWorker;
    }

    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
