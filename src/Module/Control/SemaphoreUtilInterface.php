<?php

namespace Stu\Module\Control;

use SysvSemaphore;

interface SemaphoreUtilInterface
{
    public function getSemaphore(int $key): null|int|SysvSemaphore;

    public function acquireMainSemaphore(null|int|SysvSemaphore $semaphore): void;

    public function acquireSemaphore(int $key, null|int|SysvSemaphore $semaphore): void;

    public function releaseSemaphore(null|int|SysvSemaphore $semaphore, bool $doRemove = false): void;
}
