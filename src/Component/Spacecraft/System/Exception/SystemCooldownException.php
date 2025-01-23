<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Exception;

final class SystemCooldownException extends SpacecraftSystemException
{
    public function __construct(private int $remainingSeconds) {}

    public function getRemainingSeconds(): int
    {
        return $this->remainingSeconds;
    }
}
