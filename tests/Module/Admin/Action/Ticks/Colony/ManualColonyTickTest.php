<?php

declare(strict_types=1);

namespace Stu\Module\Admin\Action\Ticks\Colony;

use Mockery\MockInterface;
use Stu\Module\Admin\View\Ticks\ShowTicks;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tick\Colony\ColonyTickInterface;
use Stu\Module\Tick\Colony\ColonyTickManagerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;
use Stu\Orm\Repository\CommodityRepositoryInterface;
use Stu\StuTestCase;

class ManualColonyTickTest extends StuTestCase
{
    /** @var MockInterface&ManualColonyTickRequestInterface */
    private ManualColonyTickRequestInterface $request;

    /** @var MockInterface&ColonyTickManagerInterface */
    private ColonyTickManagerInterface $colonyTickManager;

    /** @var MockInterface&ColonyTickInterface */
    private ColonyTickInterface $colonyTick;

    /** @var MockInterface&ColonyRepositoryInterface */
    private ColonyRepositoryInterface $colonyRepository;

    /** @var MockInterface&CommodityRepositoryInterface */
    private CommodityRepositoryInterface $commodityRepository;

    /** @var MockInterface&GameControllerInterface */
    private GameControllerInterface $game;

    private ManualColonyTick $subject;

    protected function setUp(): void
    {
        $this->request = $this->mock(ManualColonyTickRequestInterface::class);
        $this->colonyTickManager = $this->mock(ColonyTickManagerInterface::class);
        $this->colonyTick = $this->mock(ColonyTickInterface::class);
        $this->colonyRepository = $this->mock(ColonyRepositoryInterface::class);
        $this->commodityRepository = $this->mock(CommodityRepositoryInterface::class);

        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new ManualColonyTick(
            $this->request,
            $this->colonyTickManager,
            $this->colonyTick,
            $this->colonyRepository,
            $this->commodityRepository,
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

        $this->colonyTickManager->shouldReceive('work')
            ->with()
            ->once();

        $this->game->shouldReceive('addInformation')
            ->with('Der Kolonie-Tick für alle Kolonien wurde durchgeführt!')
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

        $this->commodityRepository->shouldReceive('getAll')
            ->withNoArgs()
            ->once()
            ->andReturn([42]);

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
        $colony = $this->mock(ColonyInterface::class);

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

        $this->commodityRepository->shouldReceive('getAll')
            ->withNoArgs()
            ->once()
            ->andReturn([42]);

        $this->colonyRepository->shouldReceive('find')
            ->with($colonyId)
            ->once()
            ->andReturn($colony);

        $this->colonyTick->shouldReceive('work')
            ->with($colony, [42])
            ->once();

        $this->game->shouldReceive('addInformationf')
            ->with('Der Kolonie-Tick für die Kolonie mit der ID %d wurde durchgeführt!', $colonyId)
            ->once();

        $this->subject->handle($this->game);
    }
}
