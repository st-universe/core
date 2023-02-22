<?php

namespace Stu\Module\Control;

use SysvSemaphore;

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

    public function releaseSemaphore(SysvSemaphore $semaphore, bool $doRemove = false): void;
}
