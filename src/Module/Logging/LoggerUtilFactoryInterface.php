<?php

namespace Stu\Module\Logging;

interface LoggerUtilFactoryInterface
{
    public function getLoggerUtil(): LoggerUtilInterface;
}
