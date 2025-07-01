<?php

namespace Stu\Module\Control;

use SysvSemaphore;

interface SemaphoreUtilInterface
{
    public function acquireSemaphore(int $key): null|int|SysvSemaphore;

    public function releaseSemaphore(null|int|SysvSemaphore $semaphore, bool $doRemove = false): void;
}
