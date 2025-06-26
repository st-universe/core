<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

use Mockery\MockInterface;
use Override;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Config\Model\ColonySettings;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Orm\Entity\Colony;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\StuTestCase;

class ManualColonyTickTest extends StuTestCase
{
    private MockInterface&ManualColonyTickRequestInterface $request;

    private MockInterface&ColonyTickManagerInterface $colonyTickManager;

    private MockInterface&ColonyTickInterface $colonyTick;

    private MockInterface&ColonyRepositoryInterface $colonyRepository;

    private MockInterface&StuConfigInterface $config;

    private MockInterface&GameControllerInterface $game;

    private ManualColonyTick $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->request = $this->mock(ManualColonyTickRequestInterface::class);
        $this->colonyTickManager = $this->mock(ColonyTickManagerInterface::class);
        $this->colonyTick = $this->mock(ColonyTickInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->config = $this->mock(StuConfigInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ManualColonyTick(
            $this->request,
            $this->colonyTickManager,
            $this->colonyTick,
            $this->colonyRepository,
            $this->config
        );
    }

    public function testHandleDoNothingWhenNotAdmin(): void
    {
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $this->game->shouldReceive('setView')
            ->with(ShowTicks::VIEW_IDENTIFIER)
            ->once();

        $this->game->shouldReceive('addInformation')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleExecutesForAllColoniesWhenRequestParameterEmpty(): void
    {
        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->game->shouldReceive('setView')
            ->with(ShowTicks::VIEW_IDENTIFIER)
            ->once();

        $this->request->shouldReceive('getColonyId')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->request->shouldReceive('getGroupId')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->colonyTickManager->shouldReceive('work')
            ->with(1, ColonySettings::SETTING_TICK_WORKER_DEFAULT)
            ->once();

        $this->game->shouldReceive('addInformation')
            ->with('Der Kolonie-Tick für alle Kolonien wurde durchgeführt!')
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleExecutesForColonyGroupWhenGroupParamSet(): void
    {
        $groupId = 5;
        $groupCount = 42;

        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->game->shouldReceive('setView')
            ->with(ShowTicks::VIEW_IDENTIFIER)
            ->once();

        $this->request->shouldReceive('getColonyId')
            ->withNoArgs()
            ->once()
            ->andReturn(null);

        $this->request->shouldReceive('getGroupId')
            ->withNoArgs()
            ->once()
            ->andReturn($groupId);

        $this->config->shouldReceive('getGameSettings->getColonySettings->getTickWorker')
            ->withNoArgs()
            ->once()
            ->andReturn($groupCount);

        $this->colonyTickManager->shouldReceive('work')
            ->with($groupId, $groupCount)
            ->once();

        $this->game->shouldReceive('addInformationf')
            ->with('Der Kolonie-Tick für die Kolonie-Gruppe %d/%d wurde durchgeführt!', $groupId, $groupCount)
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleDoNothingWhenColonyDoesNotExist(): void
    {
        $colonyId = 5;

        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->game->shouldReceive('setView')
            ->with(ShowTicks::VIEW_IDENTIFIER)
            ->once();

        $this->request->shouldReceive('getColonyId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturn(null);

        $this->game->shouldReceive('addInformationf')
            ->with('Keine Kolonie mit der ID %d vorhanden!', $colonyId)
            ->once();

        $this->subject->handle($this->game);
    }

    public function testHandleExecutesForSingleColonyWhenColonyExists(): void
    {
        $colonyId = 5;
        $colony = $this->mock(Colony::class);

        $this->game->shouldReceive('isAdmin')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->game->shouldReceive('setView')
            ->with(ShowTicks::VIEW_IDENTIFIER)
            ->once();

        $this->request->shouldReceive('getColonyId')
            ->withNoArgs()
            ->once()
            ->andReturn($colonyId);

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturn($colony);

        $this->colonyTick->shouldReceive('work')
            ->with($colony)
            ->once();

        $this->game->shouldReceive('addInformationf')
            ->with('Der Kolonie-Tick für die Kolonie mit der ID %d wurde durchgeführt!', $colonyId)
            ->once();

        $this->subject->handle($this->game);
    }
}
