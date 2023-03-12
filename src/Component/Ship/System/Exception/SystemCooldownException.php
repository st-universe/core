<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Exception;

final class SystemCooldownException extends ShipSystemException
{
    private int $remainingSeconds;

    public function __construct(int $remainingSeconds)
    {
        $this->remainingSeconds = $remainingSeconds;
    }

    public function getRemainingSeconds(): int
    {
        return $this->remainingSeconds;
    }
}
