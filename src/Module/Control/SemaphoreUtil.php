<?php

namespace Stu\Module\Control;

use RuntimeException;
use Stu\Component\Game\SemaphoreConstants;
use Stu\Exception\SemaphoreException;
use Stu\Module\Logging\LoggerEnum;
use Stu\Module\Logging\LoggerUtilFactoryInterface;
use Stu\Module\Logging\LoggerUtilInterface;

final class SemaphoreUtil implements SemaphoreUtilInterface
{
    private GameControllerInterface $game;

    private LoggerUtilInterface $loggerUtil;

    public function __construct(
        GameControllerInterface $game,
        LoggerUtilFactoryInterface $loggerUtilFactory
    ) {
        $this->game = $game;
        $this->loggerUtil = $loggerUtilFactory->getLoggerUtil();
    }

    public function getSemaphore(int $key)
    {
        $semaphore = sem_get(
            $key,
            1,
            0666,
            SemaphoreConstants::AUTO_RELEASE_SEMAPHORES
        );

        if ($semaphore === false) {
            throw new RuntimeException('Error getting semaphore');
        }

        return $semaphore;
    }

    public function acquireMainSemaphore($semaphore): void
    {
        $this->acquire($semaphore);
    }

    public function acquireSemaphore(int $key, $semaphore): void
    {
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

    public function releaseSemaphore($semaphore, bool $doRemove = false): void
    {
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
}
