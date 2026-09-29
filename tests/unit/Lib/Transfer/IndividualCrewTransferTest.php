<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Transfer\Wrapper\StorageEntityWrapperInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Stu\StuTestCase;

class IndividualCrewTransferTest extends StuTestCase
{
    public function testInvalidSelectionsDoNotMoveCrewAndValidSelectionDoes(): void
    {
        $positionRepository = $this->mock(ShipRumpCategoryRoleCrewRepositoryInterface::class);
        $rankRepository = $this->mock(UserCrewRankRepositoryInterface::class);
        $assignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $source = $this->mock(StorageEntityWrapperInterface::class);
        $target = $this->mock(StorageEntityWrapperInterface::class);
        $information = $this->mock(InformationInterface::class);
        $user = $this->mock(User::class);
        $sourceEntity = $this->mock(Colony::class);
        $targetEntity = $this->mock(Ship::class);
        $rump = $this->mock(SpacecraftRump::class);
        $crew = $this->mock(Crew::class);
        $secondCrew = $this->mock(Crew::class);
        $assignment = new CrewAssignment()->setCrew($crew);
        $secondAssignment = new CrewAssignment()->setCrew($secondCrew);

        $user->shouldReceive('getId')->zeroOrMoreTimes()->andReturn(101);
        $crew->shouldReceive('getId')->zeroOrMoreTimes()->andReturn(42);
        $crew->shouldReceive('getUserId')->zeroOrMoreTimes()->andReturn(101);
        $secondCrew->shouldReceive('getId')->zeroOrMoreTimes()->andReturn(43);
        $secondCrew->shouldReceive('getUserId')->zeroOrMoreTimes()->andReturn(101);
        $sourceEntity->shouldReceive('getCrewAssignments')->zeroOrMoreTimes()->andReturn(new ArrayCollection([$assignment, $secondAssignment]));
        $targetEntity->shouldReceive('getCrewAssignments')->zeroOrMoreTimes()->andReturn(new ArrayCollection());
        $targetEntity->shouldReceive('getRump')->zeroOrMoreTimes()->andReturn($rump);
        $rump->shouldReceive('getShipRumpRole')->zeroOrMoreTimes()->andReturn(null);

        $source->shouldReceive('get')->zeroOrMoreTimes()->andReturn($sourceEntity);
        $source->shouldReceive('getUser')->zeroOrMoreTimes()->andReturn($user);
        $source->shouldReceive('getMaxTransferrableCrew')->with(false, $user)->zeroOrMoreTimes()->andReturn(2);
        $source->shouldReceive('getFreeCrewSpace')->with($user)->zeroOrMoreTimes()->andReturn(0);
        $source->shouldReceive('checkCrewStorage')->with(1, true, $information)->once()->andReturn(true);
        $source->shouldReceive('postCrewTransfer')->with(0, $target, $information)->once();
        $source->shouldReceive('getName')->once()->andReturn('Kolonie');

        $target->shouldReceive('get')->zeroOrMoreTimes()->andReturn($targetEntity);
        $target->shouldReceive('getUser')->zeroOrMoreTimes()->andReturn($user);
        $target->shouldReceive('getFreeCrewSpace')->with($user)->zeroOrMoreTimes()->andReturn(1);
        $target->shouldReceive('checkCrewStorage')->with(1, false, $information)->once()->andReturn(true);
        $target->shouldReceive('acceptsCrewFrom')->with(1, $user, $information)->once()->andReturn(true);
        $target->shouldReceive('postCrewTransfer')->with(0, $source, $information)->once();
        $target->shouldReceive('getName')->once()->andReturn('Schiff');

        $troopTransferUtility->shouldReceive('assignCrew')
            ->with($assignment, $targetEntity, CrewTypeEnum::CREWMAN)
            ->once();
        $information->shouldReceive('addInformation')
            ->with('Die Crewzuordnung hat sich geändert. Bitte öffne das Transferfenster erneut')
            ->once();
        $information->shouldReceive('addInformation')
            ->with('Mindestcrew oder Crewkapazität würden verletzt. Bitte passe die Auswahl an')
            ->once();
        $information->shouldReceive('addInformation')
            ->with('Es wurden keine Änderungen ausgewählt')
            ->once();
        $information->shouldReceive('addInformationf')
            ->with('%d Crew von %s zu %s transferiert', 1, 'Kolonie', 'Schiff')
            ->once();

        $subject = new IndividualCrewTransfer(
            $positionRepository,
            $rankRepository,
            $assignmentRepository,
            $troopTransferUtility
        );

        $subject->transfer('[{"id":42,"side":1,"slot":7,"originalSide":1,"originalSlot":7}]', $source, $target, $information);
        $troopTransferUtility->shouldNotHaveReceived('assignCrew');

        $subject->transfer('[{"id":42,"side":0,"slot":7,"originalSide":0,"originalSlot":7},{"id":43,"side":0,"slot":7,"originalSide":0,"originalSlot":7}]', $source, $target, $information);
        $troopTransferUtility->shouldNotHaveReceived('assignCrew');

        $subject->transfer('[{"id":42,"side":1,"slot":7,"originalSide":0,"originalSlot":7},{"id":43,"side":1,"slot":7,"originalSide":0,"originalSlot":7}]', $source, $target, $information);
        $troopTransferUtility->shouldNotHaveReceived('assignCrew');

        $subject->transfer('[{"id":42,"side":1,"slot":7,"originalSide":0,"originalSlot":7},{"id":43,"side":0,"slot":7,"originalSide":0,"originalSlot":7}]', $source, $target, $information);
    }
}
