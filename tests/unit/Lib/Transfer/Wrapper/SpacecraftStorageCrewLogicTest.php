<?php

declare(strict_types=1);

namespace Stu\Lib\Transfer\Wrapper;

use Doctrine\Common\Collections\ArrayCollection;
use Stu\Component\Realtime\SpacecraftMovementPublisherInterface;
use Stu\Component\Spacecraft\Crew\SpacecraftCrewCalculatorInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Component\Station\Dock\DockPrivilegeUtilityInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftShutdownInterface;
use Stu\Module\Spacecraft\Lib\Auxiliary\SpacecraftStartupInterface;
use Stu\Module\Spacecraft\Lib\Crew\TroopTransferUtilityInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Station;
use Stu\StuTestCase;

final class SpacecraftStorageCrewLogicTest extends StuTestCase
{
    public function testRemovingOneUplinkUserInvalidatesCoverageEvenWhenAnotherUserKeepsTheUplinkActive(): void
    {
        $troops = $this->mock(TroopTransferUtilityInterface::class);
        $startup = $this->mock(SpacecraftStartupInterface::class);
        $publisher = $this->mock(SpacecraftMovementPublisherInterface::class);
        $station = $this->mock(Station::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $information = $this->mock(InformationInterface::class);
        $wrapper->shouldReceive('get')->andReturn($station);
        $station->shouldReceive('getCrewCount')->andReturn(2);
        $station->shouldReceive('getCrewAssignments')->andReturn(new ArrayCollection());
        $station->shouldReceive('getBuildplan')->andReturn(null);
        $station->shouldReceive('getSystemState')->with(SpacecraftSystemTypeEnum::UPLINK)->andReturn(true);
        $station->shouldReceive('hasSpacecraftSystem')->with(SpacecraftSystemTypeEnum::UPLINK)->andReturn(true);
        $station->shouldReceive('hasSpacecraftSystem')->with(SpacecraftSystemTypeEnum::TROOP_QUARTERS)->andReturn(false);
        $station->shouldNotReceive('getSpacecraftSystem');
        $troops->shouldReceive('foreignerCount')->with($station)->andReturn(1);
        $publisher->shouldReceive('publishCoverageChanged')->with($station)->once();
        $information->shouldReceive('addInformationf')->with('Der Uplink %s%s', 'ist aktiviert', '')->once();
        $startup->shouldReceive('startup')->with($wrapper)->once();
        $logic = new SpacecraftStorageCrewLogic(
            $troops,
            $this->mock(DockPrivilegeUtilityInterface::class),
            $this->mock(ActivatorDeactivatorHelperInterface::class),
            $this->mock(SpacecraftCrewCalculatorInterface::class),
            $this->mock(SpacecraftShutdownInterface::class),
            $startup,
            $publisher
        );
        $logic->postCrewTransfer($wrapper, -1, $information);
    }
}
