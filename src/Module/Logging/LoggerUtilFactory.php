<?php

declare(strict_types=1);

namespace Stu\Module\Logging;

use JBBCode\Parser;
use Override;
use Stu\Module\Config\StuConfigInterface;

final class LoggerUtilFactory implements LoggerUtilFactoryInterface
{
    public function __construct(
        private StuConfigInterface $config,
        private Parser $parser
    ) {}

    #[Override]
    public function getLoggerUtil(bool $doDefaultInit = false): LoggerUtilInterface
    {
        $loggerUtil = new LoggerUtil(
            $this->config
        );

        if ($doDefaultInit) {
            $loggerUtil->init('STU', LogLevelEnum::ERROR);
        }

        return $loggerUtil;
    }

    #[Override]
    public function getPirateLogger(): PirateLoggerInterface
    {
        $loggerUtil = new PirateLogger(
            $this->parser
        );

        $loggerUtil->init();

        return $loggerUtil;
    }
}
