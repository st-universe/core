<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Stu\Component\Crew\Skill\CrewEnhancementInterface;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\SpacecraftAttacker;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnhanceDestroyingCrew implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private CrewEnhancementInterface $crewEnhancement) {}

    #[\Override]
    public function handleSpacecraftDestruction(
        ?SpacecraftDestroyerInterface $destroyer,
        SpacecraftWrapperInterface $destroyedSpacecraftWrapper,
        SpacecraftDestructionCauseEnum $cause,
        InformationInterface $informations
    ): void {
        if (!$destroyer instanceof SpacecraftAttacker) {
            return;
        }

        $destroyedPrestige = $destroyedSpacecraftWrapper->get()->getRump()->getPrestige();
        $attackingSpacecraft = $destroyer->getSpacecraft();
        $attackerPrestige = $attackingSpacecraft->getRump()->getPrestige();
        if ($destroyedPrestige <= 0 || $attackerPrestige <= 0) {
            return;
        }

        $this->crewEnhancement->addExpertise(
            $attackingSpacecraft,
            SkillEnhancementEnum::SPACECRAFT_DESTRUCTION,
            min(200, (int)ceil($destroyedPrestige * 100 / $attackerPrestige))
        );
    }
}
