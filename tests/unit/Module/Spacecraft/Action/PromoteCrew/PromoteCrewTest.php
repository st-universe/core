<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\PromoteCrew;

use Mockery\MockInterface;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Module\Spacecraft\View\ShowCrewmanDetails\ShowCrewmanDetails;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\CrewRepositoryInterface;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class PromoteCrewTest extends ActionControllerTestCase
{
    private MockInterface&CrewRepositoryInterface $crewRepository;

    private MockInterface&UserCrewRankRepositoryInterface $userCrewRankRepository;

    private PromoteCrew $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->crewRepository = $this->mock(CrewRepositoryInterface::class);
        $this->userCrewRankRepository = $this->mock(UserCrewRankRepositoryInterface::class);
        $this->subject = new PromoteCrew($this->crewRepository, $this->userCrewRankRepository);
    }

    public function testPromotesCrewWithEnoughExpertiseBelowRankLimit(): void
    {
        $user = $this->createUser();
        $crew = $this->createCrew($user, CrewSkillLevelEnum::LIEUTENANT_COMMANDER, 10_000);
        request::setMockVars(['id' => 42]);

        $this->game->shouldReceive('setView')->with(ShowCrewmanDetails::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformationf')
            ->with('%s wurde zum %s befördert', 'Crew', 'Commander')
            ->once();
        $this->crewRepository->shouldReceive('find')->with(42)->andReturn($crew)->once();
        $this->crewRepository->shouldReceive('getAmountByUserAndRank')
            ->with($user, CrewSkillLevelEnum::COMMANDER)
            ->andReturn(24)
            ->once();
        $crew->shouldReceive('setRank')->with(CrewSkillLevelEnum::COMMANDER)->once();
        $this->crewRepository->shouldReceive('save')->with($crew)->once();

        $this->subject->handle($this->game);
    }

    public function testDoesNotPromoteCrewWithoutRequiredExpertise(): void
    {
        $user = $this->createUser();
        $crew = $this->createCrew($user, CrewSkillLevelEnum::LIEUTENANT_COMMANDER, 9_999);
        request::setMockVars(['id' => 42]);

        $this->game->shouldReceive('setView')->with(ShowCrewmanDetails::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformationf')
            ->with('%s benötigt mindestens %d Expertise in einer Fähigkeit für die Beförderung zum %s', 'Crew', 10_000, 'Commander')
            ->once();
        $this->crewRepository->shouldReceive('find')->with(42)->andReturn($crew)->once();
        $this->crewRepository->shouldReceive('getAmountByUserAndRank')->never();
        $crew->shouldReceive('setRank')->never();
        $this->crewRepository->shouldReceive('save')->never();

        $this->subject->handle($this->game);
    }

    public function testDoesNotPromoteCrewWhenRankLimitHasBeenReached(): void
    {
        $user = $this->createUser();
        $crew = $this->createCrew($user, CrewSkillLevelEnum::LIEUTENANT_COMMANDER, 10_000);
        request::setMockVars(['id' => 42]);

        $this->game->shouldReceive('setView')->with(ShowCrewmanDetails::VIEW_IDENTIFIER)->once();
        $this->game->shouldReceive('getUser')->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformationf')
            ->with('Es können gleichzeitig maximal %d Crewman im Rang %s geführt werden', 25, 'Commander')
            ->once();
        $this->crewRepository->shouldReceive('find')->with(42)->andReturn($crew)->once();
        $this->crewRepository->shouldReceive('getAmountByUserAndRank')
            ->with($user, CrewSkillLevelEnum::COMMANDER)
            ->andReturn(25)
            ->once();
        $crew->shouldReceive('setRank')->never();
        $this->crewRepository->shouldReceive('save')->never();

        $this->subject->handle($this->game);
    }

    /** @return MockInterface&User */
    private function createUser(): User
    {
        $user = $this->mock(User::class);
        $user->shouldReceive('getId')->andReturn(101);
        $this->userCrewRankRepository->shouldReceive('getRankName')->andReturn('Commander');
        return $user;
    }

    /** @return MockInterface&Crew */
    private function createCrew(User $user, CrewSkillLevelEnum $rank, int $highestSkillExpertise): Crew
    {
        $crew = $this->mock(Crew::class);
        $crew->shouldReceive('getUser')->andReturn($user);
        $crew->shouldReceive('getRank')->andReturn($rank);
        $crew->shouldReceive('getHighestSkillExpertise')->andReturn($highestSkillExpertise);
        $crew->shouldReceive('getName')->andReturn('Crew');

        return $crew;
    }
}
