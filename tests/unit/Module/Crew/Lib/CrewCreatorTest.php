<?php

declare(strict_types=1);

namespace Stu\Module\Crew\Lib;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Control\StuRandom;
use Stu\Module\Spacecraft\Lib\Crew\EntityWithCrewAssignmentsInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\CrewRaceRepositoryInterface;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\Orm\Repository\UserRepositoryInterface;
use Stu\StuTestCase;

final class CrewCreatorTest extends StuTestCase
{
    public function testRespectsAlreadyAssignedPositionsAndUsesCrewmanForTheRemainingCrew(): void
    {
        $crewRaceRepository = $this->mock(CrewRaceRepositoryInterface::class);
        $positionRepository = $this->mock(ShipRumpCategoryRoleCrewRepositoryInterface::class);
        $assignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $crewRepository = $this->mock(CrewRepositoryInterface::class);
        $userRepository = $this->mock(UserRepositoryInterface::class);
        $random = $this->mock(StuRandom::class);
        $troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $subject = new CrewCreator(
            $crewRaceRepository,
            $positionRepository,
            $assignmentRepository,
            $crewRepository,
            $userRepository,
            $random,
            $troopTransferUtility
        );

        $spacecraft = $this->mock(Spacecraft::class);
        $rump = $this->mock(SpacecraftRump::class);
        $rumpRole = $this->mock(ShipRumpRole::class);
        $rumpCategory = $this->mock(ShipRumpCategory::class);
        $user = $this->mock(User::class);
        $crewAssignments = new ArrayCollection();
        $spacecraftAssignments = new ArrayCollection();

        $spacecraft->shouldReceive('getRump')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($rump);
        $spacecraft->shouldReceive('getUser')
            ->withNoArgs()
            ->times(3)
            ->andReturn($user);
        $spacecraft->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraftAssignments);
        $rump->shouldReceive('getShipRumpRole')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpRole);
        $rump->shouldReceive('getShipRumpCategory')
            ->withNoArgs()
            ->once()
            ->andReturn($rumpCategory);
        $rumpRole->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftRumpRoleEnum::PHASER_SHIP);
        $rumpCategory->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(SpacecraftRumpCategoryEnum::RUNABOUT);

        $positionConfig = $this->mock(ShipRumpCategoryRoleCrew::class);
        $positionConfig->shouldReceive('getCrewForPosition')
            ->withAnyArgs()
            ->andReturnUsing(static fn (CrewTypeEnum $position): int => match ($position) {
                CrewTypeEnum::CAPTAIN => 2,
                CrewTypeEnum::TACTIC => 1,
                default => 0
            });
        $positionRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with(SpacecraftRumpCategoryEnum::RUNABOUT, SpacecraftRumpRoleEnum::PHASER_SHIP)
            ->once()
            ->andReturn($positionConfig);

        $existingCaptain = $this->mock(CrewAssignment::class);
        $existingCaptain->shouldReceive('getSlot')
            ->withNoArgs()
            ->once()
            ->andReturn(CrewTypeEnum::CAPTAIN);
        $spacecraftAssignments->add($existingCaptain);

        $assignedPositions = [CrewTypeEnum::CAPTAIN, CrewTypeEnum::TACTIC, CrewTypeEnum::CREWMAN];
        foreach ($assignedPositions as $index => $position) {
            $crew = $this->mock(Crew::class);
            foreach (array_slice($assignedPositions, 0, $index + 1) as $checkedPosition) {
                $crew->shouldReceive('isSkilledAt')
                    ->with($checkedPosition)
                    ->once()
                    ->andReturn(false);
            }
            $assignment = $this->mock(CrewAssignment::class);
            $assignment->shouldReceive('getCrew')
                ->withNoArgs()
                ->zeroOrMoreTimes()
                ->andReturn($crew);
            $assignment->shouldReceive('setUser')
                ->with($user)
                ->once()
                ->andReturnSelf();
            $troopTransferUtility->shouldReceive('assignCrew')
                ->with($assignment, $spacecraft, $position)
                ->once()
                ->andReturnUsing(function (CrewAssignment $assignment) use ($spacecraftAssignments): void {
                    $spacecraftAssignments->add($assignment);
                });
            $crewAssignments->set($index, $assignment);
        }

        $provider = $this->mock(EntityWithCrewAssignmentsInterface::class);
        $provider->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn($crewAssignments);
        $random->shouldReceive('array_rand')
            ->withAnyArgs()
            ->times(3)
            ->andReturnUsing(static fn (array $assignments): int => array_key_first($assignments));
        $subject->createCrewAssignments($spacecraft, $provider, 3);

        $this->assertCount(4, $spacecraftAssignments);
    }
}
