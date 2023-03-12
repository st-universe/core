<?php

namespace Stu\Module\Control;

interface SemaphoreUtilInterface
{
    public function getSemaphore(int $key);

    public function acquireMainSemaphore($semaphore): void;

    public function acquireSemaphore(int $key, $semaphore): void;

    public function releaseSemaphore($semaphore, bool $doRemove = false): void;
}
