<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill;

use Stu\Config\Init;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\SpacecraftInterface;

abstract class CrewEnhancemenProxy
{
    public static function addExpertise(
        SpacecraftInterface|SpacecraftWrapperInterface $target,
        SkillEnhancementEnum $trigger,
        int $percentage = 100
    ): void {

        $crewEnhancement =
            Init::getContainer()->get(CrewEnhancementInterface::class);


        $crewEnhancement->addExpertise($target, $trigger, $percentage);
    }
}
