<?php

declare(strict_types=1);

namespace Stu\Component\Research;

enum ResearchModeEnum: int
{
    case EXCLUDE = 0;
    case REQUIRE = 1;
    case REQUIRE_SOME = 2;

    public function getTechtreeEdgeColor(): ?string
    {
        return match ($this) {
            self::EXCLUDE => 'red',
            self::REQUIRE => null,
            self::REQUIRE_SOME => 'blue',
        };
    }
}
