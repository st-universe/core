<?php

declare(strict_types=1);

namespace Stu\Component\Research;

enum ResearchModeEnum: int
{
    case EXCLUDE = 0;
    case REQUIRE = 1;
    case REQUIRE_SOME = 2;
}
