<?php

namespace Stu\Module\Control;

use Stu\Exception\SemaphoreException;

final class SemaphoreUtil implements SemaphoreUtilInterface
{
    private GameControllerInterface $game;

    public function __construct(
        GameControllerInterface $game
    ) {
        $this->game = $game;
    }

    public function getSemaphore(int $key)
    {
        return sem_get($key, 1, 0666, 0);
    }

    public function acquireMainSemaphore($semaphore): void
    {
        $this->acquire($semaphore);
    }

    public function acquireSemaphore(int $key, $semaphore): void
    {
        if ($this->game->isSemaphoreAlreadyAcquired($key))
        {
            return;
        }

        $this->acquire($semaphore);
        $this->game->addSemaphore($key, $semaphore);
    }

    private function acquire($semaphore): void
    {
        if (!sem_acquire($semaphore))
        {
            throw new SemaphoreException("Error acquiring Semaphore!");
        }
    }
    
    public function releaseSemaphore($semaphore): void
    {
        $this->release($semaphore);
    }

    private function release($semaphore): void
    {
        if (!sem_release($semaphore))
        {
            throw new SemaphoreException("Error releasing Semaphore!");
        }

        if (!sem_remove($semaphore))
        {
            throw new SemaphoreException("Error removing Semaphore!");
        }
    }
}
