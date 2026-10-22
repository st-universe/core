<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\CloseCombat;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Message\MessageFactoryInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class BoardShipUtilTest extends StuTestCase
{
    public function testKillingAnUplinkCrewmanInvalidatesCoverageWhileOtherCrewSurvives(): void
    {
        foreach ([false, true] as $hasUplink) {
            $crewRepository = $this->mock(CrewRepositoryInterface::class);
            $assignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
            $publisher = $this->mock(SpacecraftMovementPublisherInterface::class);
            $station = $this->mock(Station::class);
            $wrapper = $this->mock(SpacecraftWrapperInterface::class);
            $killed = $this->mock(CrewAssignment::class);
            $survivor = $this->mock(CrewAssignment::class);
            $crew = $this->mock(Crew::class);
            $assignments = new ArrayCollection([$killed, $survivor]);
            $wrapper->shouldReceive('get')->andReturn($station);
            $station->shouldReceive('getCrewAssignments')->andReturn($assignments);
            $station->shouldReceive('hasSpacecraftSystem')->with(SpacecraftSystemTypeEnum::UPLINK)->andReturn($hasUplink);
            $killed->shouldReceive('getCrew')->andReturn($crew);
            $killed->shouldReceive('clearAssignment')->once();
            $assignmentRepository->shouldReceive('delete')->with($killed)->once();
            $crewRepository->shouldReceive('delete')->with($crew)->once();
            $publisher->shouldReceive('publishCoverageChanged')->with($station)->times($hasUplink ? 1 : 0);
            $subject = new BoardShipUtil($crewRepository, $assignmentRepository, $this->mock(CloseCombatUtilInterface::class), $this->mock(SpacecraftShutdownInterface::class), $this->mock(MessageFactoryInterface::class), $this->mock(StuRandom::class), $publisher);
            $combatGroup = [$killed];
            $result = $this->getMethod($subject, 'getKilledCrew')->invokeArgs($subject, [&$combatGroup, $wrapper]);
            self::assertSame($killed, $result);
            self::assertSame([], $combatGroup);
            self::assertSame([$survivor], $assignments->getValues());
        }
    }
}
