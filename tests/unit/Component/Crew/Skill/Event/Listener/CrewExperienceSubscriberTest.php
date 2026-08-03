<?php

declare(strict_types=1);

namespace Stu\Component\Crew\Skill\Event\Listener;

use Mockery\MockInterface;
use Stu\Component\Crew\Skill\CrewEnhancementInterface;
use Stu\Component\Crew\Skill\Event\CrewExperienceEvent;
use Stu\Component\Crew\Skill\SkillEnhancementEnum;
use Stu\Orm\Entity\Spacecraft;
use Stu\StuTestCase;
use Symfony\Component\EventDispatcher\EventDispatcher;

final class CrewExperienceSubscriberTest extends StuTestCase
{
    private MockInterface&CrewEnhancementInterface $crewEnhancement;

    private CrewExperienceSubscriber $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewEnhancement = $this->mock(CrewEnhancementInterface::class);
        $this->subject = new CrewExperienceSubscriber($this->crewEnhancement);
    }

    public function testAddsExpertiseForEvent(): void
    {
        $spacecraft = $this->mock(Spacecraft::class);
        $event = new CrewExperienceEvent(
            $spacecraft,
            SkillEnhancementEnum::SPACECRAFT_DESTRUCTION,
            150
        );

        $this->crewEnhancement->shouldReceive('addExpertise')
            ->with($spacecraft, SkillEnhancementEnum::SPACECRAFT_DESTRUCTION, 150)
            ->once();

        $eventDispatcher = new EventDispatcher();
        $eventDispatcher->addListener(CrewExperienceEvent::class, $this->subject->onCrewExperience(...));
        $eventDispatcher->dispatch($event);
    }
}
