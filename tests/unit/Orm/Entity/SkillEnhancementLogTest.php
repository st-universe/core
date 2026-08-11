<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use Stu\StuTestCase;

final class SkillEnhancementLogTest extends StuTestCase
{
    public function testDescriptionDoesNotDuplicatePosition(): void
    {
        $enhancement = $this->mock(SkillEnhancement::class);
        $enhancement->shouldReceive('getDescription')->andReturn('Fremdes Schiff gescannt');

        $log = new SkillEnhancementLog()
            ->setCrewName('Crew')
            ->setShipName('Aerie')
            ->setEnhancement($enhancement)
            ->setExpertise(3)
            ->setExpertiseSum(3)
            ->setPromotion(null);

        static::assertSame(
            'Crew von der Aerie hat nun 3 (+3) Expertise (Rang Kadett) für Fremdes Schiff gescannt',
            $log->getDescription('Kadett')
        );
    }
}
