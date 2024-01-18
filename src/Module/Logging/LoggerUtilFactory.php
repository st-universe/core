<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Noodlehaus\ConfigInterface;

final class LoggerUtilFactory implements LoggerUtilFactoryInterface
{
    private ConfigInterface $config;

    public function __construct(
        ConfigInterface $config
    ) {
        $this->config = $config;
    }

    public function getLoggerUtil(bool $doDefaultInit = false): LoggerUtilInterface
    {
        $loggerUtil = new LoggerUtil(
            $this->config
        );

        if ($doDefaultInit) {
            $loggerUtil->init('STU', LoggerEnum::LEVEL_ERROR);
        }

        return $loggerUtil;
    }
}
