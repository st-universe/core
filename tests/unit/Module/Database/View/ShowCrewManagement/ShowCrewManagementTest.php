<?php

declare(strict_types=1);

namespace Stu\Module\Database\View\ShowCrewManagement;

use Mockery;
use Mockery\Matcher\Closure;
use Mockery\MockInterface;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Stu\StuTestCase;

final class ShowCrewManagementTest extends StuTestCase
{
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;
    private MockInterface&UserCrewRankRepositoryInterface $userCrewRankRepository;
    private ShowCrewManagement $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->userCrewRankRepository = $this->mock(UserCrewRankRepositoryInterface::class);
        $this->subject = new ShowCrewManagement(
            $this->crewAssignmentRepository,
            $this->userCrewRankRepository
        );
    }

    public function testHandleRendersCrewSortedByHighestExpertise(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $highCrew = $this->mock(Crew::class);
        $highAssignment = $this->mock(CrewAssignment::class);
        $lowCrew = $this->mock(Crew::class);
        $lowAssignment = $this->mock(CrewAssignment::class);

        $game->shouldReceive('getUser')->once()->andReturn($user);
        $game->shouldReceive('appendNavigationPart')->with('database.php', 'Datenbank')->once();
        $game->shouldReceive('appendNavigationPart')
            ->with('database.php?SHOW_CREW_MANAGEMENT=1', 'Crew')
            ->once();
        $game->shouldReceive('setPageTitle')->with('/ Datenbank / Crew')->once();
        $game->shouldReceive('setViewTemplate')->with('html/database/crewManagement.twig')->once();
        $game->shouldReceive('setTemplateVar')->with('USER_ID', Mockery::type('int'))->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CREW_ASSIGNMENTS', new Closure(function (array $assignments) use ($highAssignment, $lowAssignment): bool {
                $this->assertSame([$highAssignment, $lowAssignment], $assignments);

                return true;
            }))
            ->once();
        $game->shouldReceive('setTemplateVar')->with('CREW_RANKS', CrewSkillLevelEnum::cases())->once();
        $game->shouldReceive('setTemplateVar')->with('CREW_RANK_NAMES', Mockery::type('array'))->once();
        $game->shouldReceive('setTemplateVar')->with('CREW_RANK_EXPERTISES', Mockery::type('array'))->once();
        $game->shouldReceive('setTemplateVar')->with('POSITIONS', Mockery::type('array'))->once();

        $this->crewAssignmentRepository->shouldReceive('findBy')
            ->with(['user' => $user])
            ->once()
            ->andReturn([$lowAssignment, $highAssignment]);
        $user->shouldReceive('getId')->once()->andReturn(42);
        $this->userCrewRankRepository->shouldReceive('getRankName')
            ->with($user, Mockery::type(CrewSkillLevelEnum::class))
            ->andReturn('Rang');
        $highAssignment->shouldReceive('getCrew')->andReturn($highCrew);
        $lowAssignment->shouldReceive('getCrew')->andReturn($lowCrew);
        $highCrew->shouldReceive('getHighestSkillExpertise')->andReturn(500);
        $lowCrew->shouldReceive('getHighestSkillExpertise')->andReturn(100);
        $highCrew->shouldReceive('getName')->andReturn('Alpha');
        $lowCrew->shouldReceive('getName')->andReturn('Beta');

        $this->subject->handle($game);
    }
}
