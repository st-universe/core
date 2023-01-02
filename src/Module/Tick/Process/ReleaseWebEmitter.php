<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Process;

use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Ship\Lib\ShipWrapperFactoryInterface;
use Stu\Module\Ship\Lib\TholianWebUtilInterface;
use Stu\Orm\Repository\TholianWebRepositoryInterface;

final class ReleaseWebEmitter implements ProcessTickInterface
{
    private TholianWebRepositoryInterface $tholianWebRepository;

    private TholianWebUtilInterface $tholianWebUtil;

    private ShipWrapperFactoryInterface $shipWrapperFactory;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        TholianWebRepositoryInterface $tholianWebRepository,
        TholianWebUtilInterface $tholianWebUtil,
        ShipWrapperFactoryInterface $shipWrapperFactory,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->tholianWebRepository = $tholianWebRepository;
        $this->tholianWebUtil = $tholianWebUtil;
        $this->shipWrapperFactory = $shipWrapperFactory;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function work(): void
    {
        foreach ($this->tholianWebRepository->getFinishedWebs() as $web) {
            $this->tholianWebUtil->resetWebHelpers($web, $this->shipWrapperFactory);
            $web->setFinishedTime(null);
            $this->tholianWebRepository->save($web);
        }
    }
}
