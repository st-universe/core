<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill\Event\Listener;

use Stu\Component\Crew\Skill\CrewEnhancementInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;

final class CrewExperienceSubscriber
{
    public function __construct(private CrewEnhancementInterface $crewEnhancement) {}

    public function onCrewExperience(CrewExperienceEvent $event): void
    {
        $this->crewEnhancement->addExpertise(
            $event->getSpacecraft(),
            $event->getTrigger(),
            $event->getPercentage()
        );
    }
}
