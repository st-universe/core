<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowCrewAssignmentManagement;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Mockery\Matcher\Closure;
use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Spacecraft\SpacecraftRumpCategoryEnum;
use Stu\Component\Spacecraft\SpacecraftRumpRoleEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\ShipRumpCategory;
use Stu\Orm\Entity\ShipRumpCategoryRoleCrew;
use Stu\Orm\Entity\ShipRumpRole;
use Stu\Orm\Entity\Spacecraft;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\ShipRumpCategoryRoleCrewRepositoryInterface;
use Stu\StuTestCase;

final class ShowCrewAssignmentManagementTest extends StuTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&ShipRumpCategoryRoleCrewRepositoryInterface $shipRumpCategoryRoleCrewRepository;

    private ShowCrewAssignmentManagement $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->shipRumpCategoryRoleCrewRepository = $this->mock(ShipRumpCategoryRoleCrewRepositoryInterface::class);
        $this->subject = new ShowCrewAssignmentManagement(
            $this->spacecraftLoader,
            $this->shipRumpCategoryRoleCrewRepository
        );
    }

    public function testListsOnlyOwnCrewOutsideTroopQuarters(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $rump = $this->mock(SpacecraftRump::class);
        $category = $this->mock(ShipRumpCategory::class);
        $role = $this->mock(ShipRumpRole::class);
        $config = $this->mock(ShipRumpCategoryRoleCrew::class);
        $ownCrew = $this->mock(Crew::class);
        $ownCrewAssignment = $this->mock(CrewAssignment::class);
        $troopCrew = $this->mock(Crew::class);
        $troopCrewAssignment = $this->mock(CrewAssignment::class);
        $foreignCrew = $this->mock(Crew::class);
        $foreignCrewAssignment = $this->mock(CrewAssignment::class);

        request::setMockVars(['id' => 42]);

        $game->shouldReceive('getUser')->andReturn($user);
        $game->shouldReceive('setPageTitle')->with('Crewposten verwalten')->once();
        $game->shouldReceive('setMacroInAjaxWindow')->with('html/spacecraft/crewAssignmentManagement.twig')->once();
        $game->shouldReceive('setTemplateVar')->with('SPACECRAFT', $spacecraft)->once();
        $game->shouldReceive('setTemplateVar')
            ->with('POSITIONS', new Closure(function (array $positions) use ($ownCrewAssignment): bool {
                $this->assertCount(7, $positions);
                $this->assertSame(CrewTypeEnum::CAPTAIN, $positions[0]['position']);
                $this->assertSame(1, $positions[0]['capacity']);
                $this->assertSame(CrewTypeEnum::COMMAND, $positions[1]['position']);
                $this->assertSame([$ownCrewAssignment], $positions[1]['crewAssignments']);
                $this->assertNull($positions[6]['capacity']);

                return true;
            }))
            ->once();
        $user->shouldReceive('getId')->andReturn(101);
        $this->spacecraftLoader->shouldReceive('getByIdAndUser')->with(42, 101, true)->andReturn($spacecraft);
        $spacecraft->shouldReceive('getRump')->andReturn($rump);
        $spacecraft->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection([
            $ownCrewAssignment,
            $troopCrewAssignment,
            $foreignCrewAssignment
        ]));
        $rump->shouldReceive('getShipRumpRole')->andReturn($role);
        $rump->shouldReceive('getShipRumpCategory')->andReturn($category);
        $category->shouldReceive('getId')->andReturn(SpacecraftRumpCategoryEnum::FRIGATE);
        $role->shouldReceive('getId')->andReturn(SpacecraftRumpRoleEnum::PHASER_SHIP);
        $this->shipRumpCategoryRoleCrewRepository->shouldReceive('getByShipRumpCategoryAndRole')
            ->with(SpacecraftRumpCategoryEnum::FRIGATE, SpacecraftRumpRoleEnum::PHASER_SHIP)
            ->andReturn($config);
        $config->shouldReceive('getCrewForPosition')->andReturnUsing(
            static fn (CrewTypeEnum $position): int => $position === CrewTypeEnum::CAPTAIN ? 1 : 0
        );
        $ownCrewAssignment->shouldReceive('getCrew')->andReturn($ownCrew);
        $ownCrewAssignment->shouldReceive('getSlot')->andReturn(CrewTypeEnum::COMMAND);
        $ownCrew->shouldReceive('getUserId')->andReturn(101);
        $troopCrewAssignment->shouldReceive('getCrew')->andReturn($troopCrew);
        $troopCrewAssignment->shouldReceive('getSlot')->andReturn(null);
        $troopCrew->shouldReceive('getUserId')->andReturn(101);
        $foreignCrewAssignment->shouldReceive('getCrew')->andReturn($foreignCrew);
        $foreignCrew->shouldReceive('getUserId')->andReturn(202);

        $this->subject->handle($game);
    }
}
