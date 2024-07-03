<?php

namespace Stu\Module\Control;

use Override;
use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Exception\SemaphoreException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LoggerEnum;
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
    public function getSemaphore(int $key): ?SysvSemaphore
    {
        if (!$this->isSemaphoreUsageActive()) {
            return null;
        }

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

    #[Override]
    public function acquireMainSemaphore($semaphore): void
    {
        if (!$this->isSemaphoreUsageActive()) {
            return;
        }

        $this->acquire($semaphore);
    }

    #[Override]
    public function acquireSemaphore(int $key, $semaphore): void
    {
        if (!$this->isSemaphoreUsageActive()) {
            return;
        }

        if ($this->game->isSemaphoreAlreadyAcquired($key)) {
            return;
        }

        $this->acquire($semaphore);
        $this->game->addSemaphore($key, $semaphore);
    }

    private function acquire($semaphore): void
    {
        if (!sem_acquire($semaphore)) {
            throw new SemaphoreException("Error acquiring Semaphore!");
        }
    }

    #[Override]
    public function releaseSemaphore($semaphore, bool $doRemove = false): void
    {
        if (!$this->isSemaphoreUsageActive()) {
            return;
        }

        $this->release($semaphore, $doRemove);
    }

    private function release($semaphore, bool $doRemove): void
    {
        if (!sem_release($semaphore)) {
            $this->loggerUtil->init('semaphores', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log("Error releasing Semaphore!");
            return;
            //throw new SemaphoreException("Error releasing Semaphore!");
        }

        if ($doRemove && !sem_remove($semaphore)) {
            $this->loggerUtil->init('semaphores', LoggerEnum::LEVEL_ERROR);
            $this->loggerUtil->log("Error removing Semaphore!");
            //throw new SemaphoreException("Error removing Semaphore!");
        }
    }

    private function isSemaphoreUsageActive(): bool
    {
        return $this->stuConfig->getGameSettings()->useSemaphores();
    }
}
