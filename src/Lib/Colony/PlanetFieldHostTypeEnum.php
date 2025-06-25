<?php

declare(strict_types=1);

namespace Stu\Lib\Colony;

enum PlanetFieldHostTypeEnum: int
{
    case COLONY = 1;
    case SANDBOX = 2;

    public function getPlanetFieldHostIdentifier(): string
    {
        return match ($this) {
            self::COLONY => 'colony',
            self::SANDBOX => 'sandbox',
        };
    }

    public function getPlanetFieldHostColumnIdentifier(): string
    {
        return match ($this) {
            self::COLONY => 'colonies_id',
            self::SANDBOX => 'colony_sandbox_id'
        };
    }
}
