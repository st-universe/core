<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\AssignCrewSlot;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Exception\AccessViolationException;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\View\ShowCrewAssignmentManagement\ShowCrewAssignmentManagement;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;

final class AssignCrewSlotTest extends ActionControllerTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository;

    private AssignCrewSlot $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->shipRumpCategoryRoleCrewRepository = $this->mock(ShipRumpCategoryRoleCrewRepositoryInterface::class);
        $this->subject = new AssignCrewSlot(
            $this->spacecraftLoader,
            $this->crewAssignmentRepository,
            $this->shipRumpCategoryRoleCrewRepository
        );
    }

    public function testAssignsOwnCrewmanToFreeSpecialPosition(): void
    {
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $config = $this->createCrewConfiguration($spacecraft, 1);

        request::setMockVars(['id' => 42, 'crewid' => 23, 'slot' => CrewTypeEnum::TACTIC->value]);

        $this->game->shouldReceive('setView')->with(ShowCrewAssignmentManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getId')->andReturn(101);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')->with(42, 101, true)->andReturn($spacecraft);
        $this->crewAssignmentRepository->shouldReceive('find')->with(23)->andReturn($crewAssignment);
        $this->crewAssignmentRepository->shouldReceive('save')->with($crewAssignment)->once();
        $crewAssignment->shouldReceive('getCrew')->andReturn($crew);
        $crewAssignment->shouldReceive('getSpacecraft')->andReturn($spacecraft);
        $crewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::CREWMAN);
        $crewAssignment->shouldReceive('setSlot')->with(CrewTypeEnum::TACTIC)->once();
        $crew->shouldReceive('getUserId')->andReturn(101);
        $crew->shouldReceive('getId')->andReturn(23);
        $spacecraft->shouldReceive('getId')->andReturn(42);
        $spacecraft->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection([$crewAssignment]));
        $config->shouldReceive('getCrewForPosition')->with(CrewTypeEnum::TACTIC)->andReturn(1);

        $this->subject->handle($this->game);
    }

    public function testDoesNotAssignCrewmanToOccupiedSpecialPosition(): void
    {
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $otherCrew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $otherCrewAssignment = $this->mock(CrewAssignment::class);
        $config = $this->createCrewConfiguration($spacecraft, 1);

        request::setMockVars(['id' => 42, 'crewid' => 23, 'slot' => CrewTypeEnum::TACTIC->value]);

        $this->game->shouldReceive('setView')->with(ShowCrewAssignmentManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getId')->andReturn(101);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')->with(42, 101, true)->andReturn($spacecraft);
        $this->crewAssignmentRepository->shouldReceive('find')->with(23)->andReturn($crewAssignment);
        $this->crewAssignmentRepository->shouldReceive('save')->never();
        $crewAssignment->shouldReceive('getCrew')->andReturn($crew);
        $crewAssignment->shouldReceive('getSpacecraft')->andReturn($spacecraft);
        $crewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::CREWMAN);
        $crewAssignment->shouldReceive('setSlot')->never();
        $crew->shouldReceive('getUserId')->andReturn(101);
        $crew->shouldReceive('getId')->andReturn(23);
        $otherCrew->shouldReceive('getId')->andReturn(24);
        $otherCrew->shouldReceive('getUserId')->andReturn(101);
        $otherCrewAssignment->shouldReceive('getCrew')->andReturn($otherCrew);
        $otherCrewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::TACTIC);
        $spacecraft->shouldReceive('getId')->andReturn(42);
        $spacecraft->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection([$crewAssignment, $otherCrewAssignment]));
        $config->shouldReceive('getCrewForPosition')->with(CrewTypeEnum::TACTIC)->andReturn(1);

        $this->subject->handle($this->game);
    }

    public function testSwapsOwnCrewmenBetweenPositions(): void
    {
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $swapCrew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $swapCrewAssignment = $this->mock(CrewAssignment::class);

        request::setMockVars([
            'id' => 42,
            'crewid' => 23,
            'slot' => CrewTypeEnum::TACTIC->value,
            'swapcrewid' => 24
        ]);

        $this->game->shouldReceive('setView')->with(ShowCrewAssignmentManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getId')->andReturn(101);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')->with(42, 101, true)->andReturn($spacecraft);
        $this->crewAssignmentRepository->shouldReceive('find')->with(23)->andReturn($crewAssignment);
        $this->crewAssignmentRepository->shouldReceive('find')->with(24)->andReturn($swapCrewAssignment);
        $this->crewAssignmentRepository->shouldReceive('save')->with($crewAssignment)->once();
        $this->crewAssignmentRepository->shouldReceive('save')->with($swapCrewAssignment)->once();

        $crewAssignment->shouldReceive('getCrew')->andReturn($crew);
        $crewAssignment->shouldReceive('getSpacecraft')->andReturn($spacecraft);
        $crewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::CREWMAN);
        $crewAssignment->shouldReceive('setSlot')->with(CrewTypeEnum::TACTIC)->once();
        $crew->shouldReceive('getUserId')->andReturn(101);

        $swapCrewAssignment->shouldReceive('getCrew')->andReturn($swapCrew);
        $swapCrewAssignment->shouldReceive('getSpacecraft')->andReturn($spacecraft);
        $swapCrewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::TACTIC);
        $swapCrewAssignment->shouldReceive('setSlot')->with(CrewTypeEnum::CREWMAN)->once();
        $swapCrew->shouldReceive('getUserId')->andReturn(101);

        $spacecraft->shouldReceive('getId')->andReturn(42);

        $this->subject->handle($this->game);
    }

    public function testRejectsSwapWithForeignCrewman(): void
    {
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $crew = $this->mock(Crew::class);
        $foreignCrew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $foreignCrewAssignment = $this->mock(CrewAssignment::class);

        request::setMockVars([
            'id' => 42,
            'crewid' => 23,
            'slot' => CrewTypeEnum::TACTIC->value,
            'swapcrewid' => 24
        ]);

        static::expectException(AccessViolationException::class);

        $this->game->shouldReceive('setView')->with(ShowCrewAssignmentManagement::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $user->shouldReceive('getId')->andReturn(101);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')->with(42, 101, true)->andReturn($spacecraft);
        $this->crewAssignmentRepository->shouldReceive('find')->with(23)->andReturn($crewAssignment);
        $this->crewAssignmentRepository->shouldReceive('find')->with(24)->andReturn($foreignCrewAssignment);
        $this->crewAssignmentRepository->shouldReceive('save')->never();

        $crewAssignment->shouldReceive('getCrew')->andReturn($crew);
        $crewAssignment->shouldReceive('getSpacecraft')->andReturn($spacecraft);
        $crewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::CREWMAN);
        $crew->shouldReceive('getUserId')->andReturn(101);
        $spacecraft->shouldReceive('getId')->andReturn(42);

        $foreignCrewAssignment->shouldReceive('getCrew')->andReturn($foreignCrew);
        $foreignCrew->shouldReceive('getUserId')->andReturn(102);

        $this->subject->handle($this->game);
    }

    /**
     * @return MockInterface&ShipRumpCategoryRoleCrew
     */
    private function createCrewConfiguration(Spacecraft $spacecraft, int $capacity): ShipRumpCategoryRoleCrew
    {
        $rump = $this->mock(SpacecraftRump::class);
        $category = $this->mock(ShipRumpCategory::class);
        $role = $this->mock(ShipRumpRole::class);
        $config = $this->mock(ShipRumpCategoryRoleCrew::class);

        $spacecraft->shouldReceive('getRump')->andReturn($rump);
        $rump->shouldReceive('getShipRumpRole')->andReturn($role);
        $rump->shouldReceive('getShipRumpCategory')->andReturn($category);
        $category->shouldReceive('getId')->andReturn(SpacecraftRumpCategoryEnum::FRIGATE);
        $role->shouldReceive('getId')->andReturn(SpacecraftRumpRoleEnum::PHASER_SHIP);
        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with(SpacecraftRumpCategoryEnum::FRIGATE, SpacecraftRumpRoleEnum::PHASER_SHIP)
            ->andReturn($config);

        return $config;
    }
}
