<?php

declare(strict_types=1);

namespace Stu\Module\PlayerSetting\Action\ChangeCrewRankNames;

use Mockery\MockInterface;
use Mockery;
use request;
use Stu\ActionControllerTestCase;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Orm\Entity\User;
use Stu\Orm\Entity\UserCrewRank;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;

final class ChangeCrewRankNamesTest extends ActionControllerTestCase
{
    private MockInterface&UserCrewRankRepositoryInterface $userCrewRankRepository;

    private ChangeCrewRankNames $subject;

    #[\Override]
    protected function setUp(): void
    {
        parent::setUp();

        $this->userCrewRankRepository = $this->mock(UserCrewRankRepositoryInterface::class);
        $this->subject = new ChangeCrewRankNames($this->userCrewRankRepository);
    }

    public function testRejectsInvalidRankName(): void
    {
        request::setMockVars([
            'crew_rank_names' => [
                CrewSkillLevelEnum::CADET->value => "O''Brian"
            ]
        ]);

        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Rangnamen dürfen nur Buchstaben, einzelne Leerzeichen, Apostrophe und Akzentzeichen enthalten')
            ->once();
        $this->game->shouldReceive('getUser')->never();

        $this->subject->handle($this->game);
    }

    public function testCreatesCustomRankName(): void
    {
        $user = $this->mock(User::class);
        $rankEntry = $this->mock(UserCrewRank::class);
        request::setMockVars([
            'crew_rank_names' => [
                CrewSkillLevelEnum::CADET->value => "O'Brian"
            ]
        ]);

        $this->game->shouldReceive('getUser')->once()->andReturn($user);
        $this->game->shouldReceive('getInfo->addInformation')
            ->with('Die Crew-Ränge wurden aktualisiert')
            ->once();
        $this->userCrewRankRepository->shouldReceive('getByUserAndRank')->with($user, Mockery::type(CrewSkillLevelEnum::class))->andReturn(null);
        $rankEntry->shouldReceive('setUser')->with($user)->once()->andReturnSelf();
        $rankEntry->shouldReceive('setRank')->with(CrewSkillLevelEnum::CADET)->once()->andReturnSelf();
        $rankEntry->shouldReceive('setName')->with("O'Brian")->once();
        $this->userCrewRankRepository->shouldReceive('prototype')->once()->andReturn($rankEntry);
        $this->userCrewRankRepository->shouldReceive('save')->with($rankEntry)->once();

        $this->subject->handle($this->game);
    }
}
