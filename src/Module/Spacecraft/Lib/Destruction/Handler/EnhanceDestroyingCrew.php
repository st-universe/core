<?php

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Override;
use Stu\Component\Crew\Skill\CrewEnhancemenProxy;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\SpacecraftAttacker;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

class EnhanceDestroyingCrew implements SpacecraftDestructionHandlerInterface
{
    #[Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {

        if ($destroyer === null) {
            return;
        }

        $destroyedPrestige = $destroyedSpacecraftWrapper->get()->getRump()->getPrestige();
        if (
            $destroyedPrestige > 0
            && $destroyer instanceof SpacecraftAttacker
        ) {

            CrewEnhancemenProxy::addExpertise(
                $destroyer->get(),
                SkillEnhancementEnum::SPACECRAFT_DESTRUCTION,
                min(200, (int)ceil($destroyedPrestige * 100 / abs($destroyer->get()->getRump()->getPrestige())))
            );
        }
    }
}
