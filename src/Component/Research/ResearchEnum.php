<?php

declare(strict_types=1);

namespace Stu\Component\Research;

final class ResearchEnum
{

    public const RESEARCH_START_FEDERATION = 1001;
    public const RESEARCH_START_ROMULAN = 1002;
    public const RESEARCH_START_KLINGON = 1003;
    public const RESEARCH_START_CARDASSIAN = 1004;
    public const RESEARCH_START_FERENGI = 1005;
    public const RESEARCH_START_EMPIRE = 1006;
    public const RESEARCH_MODE_EXCLUDE = 0;
    public const RESEARCH_MODE_REQUIRE = 1;
    public const RESEARCH_MODE_REQUIRE_SOME = 2;
}
