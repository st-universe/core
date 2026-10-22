<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Crew;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\SpacecraftRumpRepositoryInterface;
use Stu\StuTestCase;

final class SpacecraftLeaverTest extends StuTestCase
{
    public function testLeavingAnActiveUplinkRevokesTheFormerCrewOwnersCoverageWithOrWithoutEscapePods(): void
    {
        foreach ([false, true] as $hasPods) {
            $assignments = $this->mock(CrewAssignmentRepositoryInterface::class);
            $crews = $this->mock(CrewRepositoryInterface::class);
            $pods = $this->mock(LaunchEscapePodsInterface::class);
            $publisher = $this->mock(SpacecraftMovementPublisherInterface::class);
            $station = $this->mock(Station::class);
            $assignment = $this->mock(CrewAssignment::class);
            $assignment->shouldReceive('getSpacecraft')->andReturn($station);
            $station->shouldReceive('hasSpacecraftSystem')->with(SpacecraftSystemTypeEnum::UPLINK)->andReturn(true);
            if ($hasPods) {
                $pod = $this->mock(Ship::class);
                $pods->shouldReceive('launch')->with($station)->once()->andReturn($pod);
                $station->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection([$assignment]));
                $assignment->shouldReceive('setSpacecraft')->with($pod)->once();
                $assignment->shouldReceive('setSlot')->with(null)->once();
                $assignments->shouldReceive('save')->with($assignment)->once();
            } else {
                $crew = $this->mock(Crew::class);
                $pods->shouldReceive('launch')->with($station)->once()->andReturn(null);
                $assignment->shouldReceive('getCrew')->andReturn($crew);
                $assignment->shouldReceive('clearAssignment')->once();
                $assignments->shouldReceive('delete')->with($assignment)->once();
                $crews->shouldReceive('delete')->with($crew)->once();
            }
            $publisher->shouldReceive('publishCoverageChanged')->with($station)->once();
            $subject = new SpacecraftLeaver($assignments, $this->mock(SpacecraftRumpRepositoryInterface::class), $crews, $pods, $this->mock(SpacecraftShutdownInterface::class), $this->mock(PrivateMessageSenderInterface::class), $publisher);
            self::assertNotEmpty($subject->leaveSpacecraft($assignment));
        }
    }
}
