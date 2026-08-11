<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

enum SkillEnhancementEnum: int
{
    case REACH_ASTRO_WAYPOINT = 1;
    case FINISH_ASTRO_MAPPING = 2;
    case SPACECRAFT_DESTRUCTION = 3;
    case SPACECRAFT_TAKEOVER = 4;
    case SALVAGE_EMERGENCY_PODS = 5;
    case REPAIR_COMPLETED = 6;
    case FOREIGN_SPACECRAFT_SCAN = 7;
    case EVADE_ATTACK = 8;
    case ESCAPE_TRACTOR_BEAM = 9;
}
