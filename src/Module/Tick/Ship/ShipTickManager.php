<?php

declare(strict_types=1);

namespace Stu\Module\Tick\Ship;

use Override;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use Stu\Module\Tick\Lock\LockManagerInterface;
use Stu\Module\Tick\Lock\LockTypeEnum;
use Stu\Module\Tick\Ship\ManagerComponent\ManagerComponentInterface;

final class ShipTickManager implements ShipTickManagerInterface
{
    private LoggerUtilInterface $loggerUtil;

    /** @param array<ManagerComponentInterface> $components */
    public function __construct(
        private LockManagerInterface $lockManager,
        private array $components,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil(true);
    }

    #[Override]
    public function work(): void
    {
        $this->setLock(1);

        try {
            foreach ($this->components as $component) {
                $startTime = microtime(true);
                $component->work();

                if ($this->loggerUtil->doLog()) {
                    $endTime = microtime(true);
                    $this->loggerUtil->log(sprintf("\t\t%s, seconds: %F", get_class($component), $endTime - $startTime));
                }
            }
        } finally {
            $this->clearLock(1);
        }
    }

    private function setLock(int $batchGroupId): void
    {
        $this->lockManager->setLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }

    private function clearLock(int $batchGroupId): void
    {
        $this->lockManager->clearLock($batchGroupId, LockTypeEnum::SHIP_GROUP);
    }
}
