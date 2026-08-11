<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Destruction\Handler;

use Psr\EventDispatcher\EventDispatcherInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Battle\Provider\SpacecraftAttacker;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestroyerInterface;
use Stu\Module\Spacecraft\Lib\Destruction\SpacecraftDestructionCauseEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class EnhanceDestroyingCrew implements SpacecraftDestructionHandlerInterface
{
    public function __construct(private EventDispatcherInterface $eventDispatcher) {}

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
        if ($destroyedPrestige === 0) {
            return;
        }

        $this->eventDispatcher->dispatch(new CrewExperienceEvent(
            $attackingSpacecraft,
            SkillEnhancementEnum::SPACECRAFT_DESTRUCTION,
            $destroyedPrestige * 100
        ));
    }
}
