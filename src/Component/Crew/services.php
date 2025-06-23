<?php

declare(strict_types=1);

namespace Stu\Component\Crew;

use Stu\Component\Crew\Skill\CreateEnhancementLog;
use Stu\Component\Crew\Skill\CrewEnhancement;
use Stu\Component\Crew\Skill\CrewEnhancementInterface;
use Stu\Component\Crew\Skill\RaiseExpertise;
use Stu\Component\Crew\Skill\SkillEnhancementCache;
use Stu\Component\Crew\Skill\SkillEnhancementCacheInterface;

use function DI\autowire;

return [
    CrewCountRetrieverInterface::class => autowire(CrewCountRetriever::class),
    RaiseExpertise::class => autowire(RaiseExpertise::class),
    CreateEnhancementLog::class => autowire(CreateEnhancementLog::class),
    CrewEnhancementInterface::class => autowire(CrewEnhancement::class),
    SkillEnhancementCacheInterface::class => autowire(SkillEnhancementCache::class)
];
