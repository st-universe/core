<?php

declare(strict_types=1);

namespace Stu\Lib\Pirate;

use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class PirateReactionMetadataTest extends StuTestCase
{
    public function testGetReactionAmount_expectZero_whenNotSet(): void
    {
        $subject = new PirateReactionMetadata(
            PirateReactionTriggerEnum::ON_ATTACK,
            $this->mock(Ship::class)
        );

        $result = $subject->getReactionAmount(PirateBehaviourEnum::CALL_FOR_SUPPORT);

        $this->assertEquals(0, $result);
    }

    public function testAddReaction_expectSetToOne_whenNotSet(): void
    {
        $subject = new PirateReactionMetadata(
            PirateReactionTriggerEnum::ON_ATTACK,
            $this->mock(Ship::class)
        );

        $subject->addReaction(PirateBehaviourEnum::CALL_FOR_SUPPORT);
        $result = $subject->getReactionAmount(PirateBehaviourEnum::CALL_FOR_SUPPORT);

        $this->assertEquals(1, $result);
    }

    public function testAddReaction_expectIncrease_whenSet(): void
    {
        $subject = new PirateReactionMetadata(
            PirateReactionTriggerEnum::ON_ATTACK,
            $this->mock(Ship::class)
        );

        $subject->addReaction(PirateBehaviourEnum::CALL_FOR_SUPPORT);
        $subject->addReaction(PirateBehaviourEnum::CALL_FOR_SUPPORT);
        $subject->addReaction(PirateBehaviourEnum::CALL_FOR_SUPPORT);
        $result = $subject->getReactionAmount(PirateBehaviourEnum::CALL_FOR_SUPPORT);

        $this->assertEquals(3, $result);
    }
}
