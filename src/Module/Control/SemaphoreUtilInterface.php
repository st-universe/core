<?php

namespace Stu\Module\Control;

interface SemaphoreUtilInterface
{
    public function getSemaphore(int $key);

    /**
     * @param resource $semaphore
     */
    public function acquireMainSemaphore($semaphore): void;

    /**
     * @param resource $semaphore
     */
    public function acquireSemaphore(int $key, $semaphore): void;

    /**
     * @param resource $semaphore
     */
    public function releaseSemaphore($semaphore, bool $doRemove = false): void;
}
