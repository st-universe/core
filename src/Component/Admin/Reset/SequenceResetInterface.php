<?php

declare(strict_types=1);

namespace Stu\Component\Admin\Reset;

interface SequenceResetInterface
{
    public function resetSequences(): int;
}
