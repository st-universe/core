<?php

declare(strict_types=1);

namespace Stu\Component\Player\Deletion;

interface PlayerDeletionInterface
{
    public function handleIdleUsers(): void;

    public function handleReset(): void;
}
