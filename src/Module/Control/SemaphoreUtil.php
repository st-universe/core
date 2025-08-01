<?php

namespace Stu\Module\Control;

use Override;
use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Exception\SemaphoreException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LogLevelEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;
use SysvSemaphore;

final class SemaphoreUtil implements SemaphoreUtilInterface
{
    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        private GameControllerInterface $game,
        private StuConfigInterface $stuConfig,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    #[Override]
    public function acquireSemaphore(int $key): null|int|SysvSemaphore
    {
        if (!$this->isSemaphoreUsageActive()) {
            return null;
        }

        $semaphore = $this->getSemaphore($key);

        if ($this->game->isSemaphoreAlreadyAcquired($key)) {
            return null;
        }

        $this->acquire($semaphore);
        $this->game->addSemaphore($key, $semaphore);

        return $semaphore;
    }

    private function getSemaphore(int $key): SysvSemaphore
    {
        $semaphore = sem_get(
            $key,
            1,
            0o666,
            SemaphoreConstants::AUTO_RELEASE_SEMAPHORES
        );

        if ($semaphore === false) {
            throw new RuntimeException('Error getting semaphore');
        }

        return $semaphore;
    }

    private function acquire(SysvSemaphore $semaphore): void
    {
        if (!sem_acquire($semaphore)) {
            throw new SemaphoreException("Error acquiring Semaphore!");
        }
    }

    #[Override]
    public function releaseSemaphore(null|int|SysvSemaphore $semaphore, bool $doRemove = false): void
    {
        if (!$this->isSemaphoreUsageActive() || !$semaphore instanceof SysvSemaphore) {
            return;
        }

        $this->release($semaphore, $doRemove);
    }

    private function release(SysvSemaphore $semaphore, bool $doRemove): void
    {
        if (!sem_release($semaphore)) {
            $this->loggerUtil->init('semaphores', LogLevelEnum::ERROR);
            $this->loggerUtil->log("Error releasing Semaphore!");
            return;
            //throw new SemaphoreException("Error releasing Semaphore!");
        }

        if ($doRemove && !sem_remove($semaphore)) {
            $this->loggerUtil->init('semaphores', LogLevelEnum::ERROR);
            $this->loggerUtil->log("Error removing Semaphore!");
            //throw new SemaphoreException("Error removing Semaphore!");
        }
    }

    private function isSemaphoreUsageActive(): bool
    {
        return $this->stuConfig->getGameSettings()->useSemaphores();
    }
}
