<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use Stu\Module\Config\StuConfigInterface;

final class LoggerUtilFactory implements LoggerUtilFactoryInterface
{
    public function __construct(
        private StuConfigInterface $config
    ) {
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
