<?php

declare(strict_types=1);

namespace Stu\Component\Ship\System\Exception;

final class SystemCooldownException extends ShipSystemException
{
    public function __construct(private int $remainingSeconds)
    {
    }

    public function getRemainingSeconds(): int
    {
        return $this->remainingSeconds;
    }
}
