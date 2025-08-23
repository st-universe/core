<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\StandBy;

use Doctrine\Common\Collections\ArrayCollection;
use Mockery\MockInterface;
use Override;
use request;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\Spacecraft;
use Stu\StuTestCase;

class StandByTest extends StuTestCase
{
    private MockInterface&ActivatorDeactivatorHelperInterface $helper;
    private MockInterface&SpacecraftLoaderInterface $spacecraftLoader;
    private MockInterface&AlertReactionFacadeInterface $alertReactionFacade;
    private MockInterface&SpacecraftSystemManagerInterface $spacecraftSystemManager;

    private ActionControllerInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->helper = $this->mock(ActivatorDeactivatorHelperInterface::class);
        $this->spacecraftLoader = $this->mock(SpacecraftLoaderInterface::class);
        $this->alertReactionFacade = $this->mock(AlertReactionFacadeInterface::class);
        $this->spacecraftSystemManager = $this->mock(SpacecraftSystemManagerInterface::class);

        $this->subject = new StandBy(
            $this->helper,
            $this->spacecraftLoader,
            $this->alertReactionFacade,
            $this->spacecraftSystemManager
        );
    }

    public function testHandle(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $info = $this->mock(InformationWrapper::class);
        $spacecraft = $this->mock(Spacecraft::class);
        $spacecraftWrapper = $this->mock(SpacecraftWrapperInterface::class);

        $shipId = 666;
        $userId = 42;

        request::setMockVars(['id' => $shipId]);

        $game->shouldReceive('setView')
            ->with(ShowSpacecraft::VIEW_IDENTIFIER)
            ->once();
        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('getInfo')
            ->withNoArgs()
            ->andReturn($info);

        $spacecraftWrapper->shouldReceive('get')
            ->withNoArgs()
            ->once()
            ->andReturn($spacecraft);
        $spacecraftWrapper->shouldReceive('getTractoredShipWrapper')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $spacecraft->shouldReceive('hasComputer')
            ->withNoArgs()
            ->once()
            ->andReturn(true);
        $spacecraft->shouldReceive('getWarpDriveState')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $spacecraft->shouldReceive('isCloaked')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $info->shouldReceive('addInformation')
            ->with('Der Energieverbrauch wurde auf ein Minimum reduziert')
            ->once();

        $this->spacecraftLoader->shouldReceive('getWrapperByIdAndUser')
            ->with($shipId, $userId)
            ->once()
            ->andReturn($spacecraftWrapper);

        $spacecraftWrapper->shouldReceive('getComputerSystemDataMandatory->setAlertStateGreen->update')
            ->withNoArgs()
            ->once()
            ->globally()
            ->ordered();
        $this->spacecraftSystemManager->shouldReceive('getActiveSystems')
            ->with($spacecraft)
            ->once()
            ->globally()
            ->ordered()
            ->andReturn(new ArrayCollection());

        $this->subject->handle($game);
    }
}
