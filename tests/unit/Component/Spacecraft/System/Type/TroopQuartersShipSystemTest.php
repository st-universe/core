<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Type;

use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Station;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\StuTestCase;

final class TroopQuartersShipSystemTest extends StuTestCase
{
    public static function crewConfigurations(): iterable
    {
        foreach ([false, true] as $uplink) {
            foreach ([false, true] as $extraCrew) {
                yield [$uplink, $extraCrew];
            }
        }
    }

    #[DataProvider('crewConfigurations')]
    public function testCrewRemovalInvalidatesUplinkCoverageAndPreservesRegularSlots(bool $uplink, bool $extraCrew): void
    {
        $station = $this->mock(Station::class);
        $assignments = new ArrayCollection();
        $station->shouldReceive('getCrewAssignments')->andReturn($assignments);
        $regular = new CrewAssignment()->setCrew(new Crew())->setSlot(CrewTypeEnum::COMMAND)->setSpacecraft($station);
        $assignments->add($regular);
        $foreign = new CrewAssignment()->setCrew(new Crew())->setSpacecraft($station);
        if ($extraCrew) {
            $assignments->add($foreign);
            $station->shouldReceive('hasSpacecraftSystem')->with(SpacecraftSystemTypeEnum::UPLINK)->once()->andReturn($uplink);
        } else {
            $station->shouldNotReceive('hasSpacecraftSystem');
        }
        $crew = $this->mock(CrewRepositoryInterface::class);
        $repository = $this->mock(CrewAssignmentRepositoryInterface::class);
        if ($extraCrew) {
            $crew->shouldReceive('delete')->with($foreign->getCrew())->once();
            $repository->shouldReceive('delete')->with($foreign)->once();
        } else {
            $crew->shouldNotReceive('delete');
            $repository->shouldNotReceive('delete');
        }
        $publisher = $this->mock(SpacecraftMovementPublisherInterface::class);
        if ($extraCrew && $uplink) {
            $publisher->shouldReceive('publishCoverageChanged')->with($station)->once();
        } else {
            $publisher->shouldNotReceive('publishCoverageChanged');
        }
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $wrapper->shouldReceive('get')->andReturn($station);
        new TroopQuartersShipSystem($crew, $repository, $publisher)->handleDestruction($wrapper);
        self::assertSame([$regular], array_values($assignments->toArray()));
        self::assertSame($station, $regular->getSpacecraft());
        if ($extraCrew) {
            self::assertNull($foreign->getSpacecraft());
        }
    }
}
