<?php

namespace Stu\Module\Config\Model;

use Override;
use Stu\Module\Config\StuConfigException;

final class ColonySettings extends AbstractSettings implements ColonySettingsInterface
{
    public const int SETTING_TICK_WORKER_DEFAULT = 1;

    private const string CONFIG_PATH = 'colony';
    private const string SETTING_TICK_WORKER = 'tick_worker';

    #[Override]
    public function getTickWorker(): int
    {
        $tickWorker = $this->getIntegerConfigValue(self::SETTING_TICK_WORKER, self::SETTING_TICK_WORKER_DEFAULT);

        if ($tickWorker < 1) {
            throw new StuConfigException(sprintf('Invalid value for "%s.%s", should be greater than 0.', $this->getPath(), self::SETTING_TICK_WORKER));
        }

        return $tickWorker;
    }

    #[Override]
    public function getConfigPath(): string
    {
        return self::CONFIG_PATH;
    }
}
