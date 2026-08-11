<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowSpacecraftDetails;

use Mockery\MockInterface;
use Doctrine\Common\Collections\ArrayCollection;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Station\Lib\StationLoaderInterface;
use Stu\Orm\Entity\Alliance;
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

    public function testHandleAllowsUplinkForStationDetails(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $alliance = $this->mock(Alliance::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $station = $this->mock(Station::class);
        $rump = $this->mock(SpacecraftRump::class);

        $userId = 42;
        $stationId = 23;
        request::setMockVars(['id' => $stationId]);

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
            ->with('CREW_RANK_NAMES', [])
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('TRACTOR_PAYLOAD', 0)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('FOREIGNER_COUNT', 0)
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
            ->andReturn(0);

        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->times(4)
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
            ->andReturn(new ArrayCollection());
        $rump->shouldReceive('getShipRumpRole')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->subject->handle($game);
    }
}
