<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraftDetails;

use Mockery\MockInterface;
use Doctrine\Common\Collections\ArrayCollection;
use request;
use Stu\Component\Crew\CrewTypeEnum;
use Stu\Component\Crew\Skill\CrewSkillLevelEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\Crew;
use Stu\Orm\Entity\CrewAssignment;
use Stu\Orm\Entity\SpacecraftRump;
use Stu\Orm\Entity\Station;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\UserCrewRankRepositoryInterface;
use Stu\StuTestCase;

class ShowSpacecraftDetailsTest extends StuTestCase
{
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&StationLoaderInterface $stationLoader;
    private MockInterface&TroopTransferUtilityInterface $troopTransferUtility;
    private MockInterface&UserCrewRankRepositoryInterface $userCrewRankRepository;

    private ShowSpacecraftDetails $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->stationLoader = $this->mock(StationLoaderInterface::class);
        $this->troopTransferUtility = $this->mock(TroopTransferUtilityInterface::class);
        $this->userCrewRankRepository = $this->mock(UserCrewRankRepositoryInterface::class);

        $this->subject = new ShowSpacecraftDetails(
            $this->spacecraftLoader,
            $this->stationLoader,
            $this->troopTransferUtility,
            $this->userCrewRankRepository
        );
    }

    public function testHandleAllowsUplinkAndGroupsCrewByStationOwner(): void
    {
        $game = $this->mock(ViewControllerContext::class);
        $alliance = $this->mock(Alliance::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $station = $this->mock(Station::class);
        $rump = $this->mock(SpacecraftRump::class);
        $stationOwner = $this->mock(User::class);
        $guest = $this->mock(User::class);
        $ownerCrew = $this->mock(Crew::class);
        $guestCrew = $this->mock(Crew::class);
        $ownerAssignment = (new CrewAssignment())->setCrew($ownerCrew)->setSlot(CrewTypeEnum::CREWMAN);
        $guestAssignment = (new CrewAssignment())->setCrew($guestCrew)->setSlot(CrewTypeEnum::CREWMAN);

        $userId = 42;
        $stationId = 23;
        request::setMockVars(['id' => $stationId]);

        $station->shouldReceive('getUser')->withNoArgs()->once()->andReturn($stationOwner);
        $stationOwner->shouldReceive('getId')->andReturn(101);
        $guest->shouldReceive('getId')->andReturn($userId);
        $ownerCrew->shouldReceive('getUser')->andReturn($stationOwner);
        $ownerCrew->shouldReceive('getId')->andReturn(70);
        $ownerCrew->shouldReceive('getRank')->andReturn(CrewSkillLevelEnum::CADET);
        $guestCrew->shouldReceive('getUser')->andReturn($guest);
        $guestCrew->shouldReceive('getId')->andReturn(71);
        $guestCrew->shouldReceive('getRank')->andReturn(CrewSkillLevelEnum::CADET);
        $this->userCrewRankRepository->shouldReceive('getRankName')
            ->with($stationOwner, CrewSkillLevelEnum::CADET)->once()->andReturn('Kadett');
        $this->userCrewRankRepository->shouldReceive('getRankName')
            ->with($guest, CrewSkillLevelEnum::CADET)->once()->andReturn('Gast-Kadett');

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('getUser->getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $game->shouldReceive('setPageTitle')
            ->with('Schiffsinformationen')
            ->once();
        $game->shouldReceive('setMacroInAjaxWindow')
            ->with('html/spacecraft/spacecraftDetails.twig')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('WRAPPER', $wrapper)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('USER_ID', $userId)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('OWN_CREW_ASSIGNMENTS', [$ownerAssignment])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('FOREIGN_CREW_ASSIGNMENTS', [$guestAssignment])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CREW_RANK_NAMES', [70 => 'Kadett', 71 => 'Gast-Kadett'])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('TRACTOR_PAYLOAD', 0)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('FOREIGNER_COUNT', 1)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('MAX_FOREIGNERS', 3)
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($stationId, $userId, true, false)
            ->once()
            ->andReturn($wrapper);
        $this->stationLoader->shouldReceive('getByIdAndUser')
            ->with($stationId, $userId, true, false)
            ->once()
            ->andReturn($station);
        $this->troopTransferUtility->shouldReceive('foreignerCount')
            ->with($station)
            ->once()
            ->andReturn(1);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->times(5)
            ->andReturn($station);

        $station->shouldReceive('isStation')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $station->shouldReceive('getRump')
            ->withNoArgs()
            ->once()
            ->andReturn($rump);
        $station->shouldReceive('hasSpacecraftSystem')
            ->with(SpacecraftSystemTypeEnum::TRACTOR_BEAM)
            ->once()
            ->andReturn(false);
        $station->shouldReceive('getCrewAssignments')
            ->withNoArgs()
            ->once()
            ->andReturn(new ArrayCollection([$guestAssignment, $ownerAssignment]));
        $rump->shouldReceive('getShipRumpRole')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->handle($game);
    }
}
