<?php

namespace Stu\Module\Logging;

interface LoggerUtilFactoryInterface
{
    public function getLoggerUtil(bool $doDefaultInit = false): LoggerUtilInterface;

    public function getPirateLogger(): PirateLoggerInterface;
}
