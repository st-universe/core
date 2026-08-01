<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowCrewmanDetails;

use Mockery\MockInterface;
use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\SkillEnhancementLog;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewAssignmentRepositoryInterface;
use Stu\Orm\Repository\SkillEnhancementLogRepositoryInterface;
use Stu\StuTestCase;

final class ShowCrewmanDetailsTest extends StuTestCase
{
    private MockInterface&CrewAssignmentRepositoryInterface $crewAssignmentRepository;

    private MockInterface&SkillEnhancementLogRepositoryInterface $skillEnhancementLogRepository;

    private ShowCrewmanDetails $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->crewAssignmentRepository = $this->mock(CrewAssignmentRepositoryInterface::class);
        $this->skillEnhancementLogRepository = $this->mock(SkillEnhancementLogRepositoryInterface::class);
        $this->subject = new ShowCrewmanDetails($this->crewAssignmentRepository, $this->skillEnhancementLogRepository);
    }

    public function testUsesFiveLogEntriesByDefault(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $crew = $this->mock(Crew::class);
        $crewAssignment = $this->mock(CrewAssignment::class);
        $logs = [$this->mock(SkillEnhancementLog::class)];
        request::setMockVars(['id' => 42]);

        $game->shouldReceive('setMacroInAjaxWindow')->with('html/entityNotAvailable.twig')->once();
        $game->shouldReceive('getUser')->andReturn($user);
        $game->shouldReceive('setPageTitle')->with('Crewman Details')->once();
        $game->shouldReceive('setViewTemplate')->with('html/spacecraft/crewmanDetails.twig')->once();
        $game->shouldReceive('setTemplateVar')->with('CREW_ASSIGNMENT', $crewAssignment)->once();
        $game->shouldReceive('setTemplateVar')->with('COUNT', 5)->once();
        $game->shouldReceive('setTemplateVar')->with('LOGS', $logs)->once();
        $user->shouldReceive('getId')->andReturn(101);
        $crew->shouldReceive('getUserId')->andReturn(101);
        $crewAssignment->shouldReceive('getCrew')->andReturn($crew);
        $this->crewAssignmentRepository->shouldReceive('find')->with(42)->andReturn($crewAssignment)->once();
        $this->skillEnhancementLogRepository->shouldReceive('getForCrewman')->with($crew)->andReturn($logs)->once();

        $this->subject->handle($game);
    }
}
