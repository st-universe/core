<?php

declare(strict_types=1);

namespace Stu\Orm\Entity;

use PHPUnit\Framework\TestCase;

final class CrewRaceTest extends TestCase
{
    public function testUsesCustomCrewImagePathForUserCreatedRace(): void
    {
        $subject = (new CrewRace())
            ->setGfxPath('TEST_RASSE')
            ->setCreatorUserId(42);

        self::assertSame('/avatare/user/crew/TEST_RASSE/m/1_3.png', $subject->getImagePath('m', 3));
    }

    public function testUsesAssetCrewImagePathForStandardRace(): void
    {
        $subject = (new CrewRace())->setGfxPath('TEST_RASSE');

        self::assertSame('/assets/crew/TEST_RASSE/w/1_6.png', $subject->getImagePath('w', 6));
    }

    public function testNormalizesFactionIds(): void
    {
        $subject = (new CrewRace())->setFactionIds([1, 2, 1]);

        self::assertSame([1, 2], $subject->getFactionIds());
        self::assertTrue($subject->hasFactionId(2));
        self::assertFalse($subject->hasFactionId(3));
    }

    public function testRejectedRaceHasModeratorId(): void
    {
        $subject = (new CrewRace())
            ->setAccepted(false)
            ->setAcceptedUserId(8);

        self::assertTrue($subject->isRejected());
        self::assertSame('Abgelehnt', $subject->getStatus());
    }

    public function testIsCivilByDefault(): void
    {
        $subject = new CrewRace();

        self::assertTrue($subject->isCivil());
        self::assertFalse($subject->setCivil(false)->isCivil());
    }
}
