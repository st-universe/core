<?php

namespace Stu\Module\Control;

use SysvSemaphore;

interface SemaphoreUtilInterface
{
    public function isSemaphoreAlreadyAcquired(int $key): bool;

    public function acquireSemaphore(int $key): null|int|SysvSemaphore;

    public function releaseSemaphore(null|int|SysvSemaphore $semaphore, bool $doRemove = false): void;

    public function releaseAllSemaphores(bool $doRemove = false): void;
}
