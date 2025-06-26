<?php

declare(strict_types=1);

namespace Stu\Module\Communication\View\ShowWriteKn;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\RpgPlot;
use Stu\Orm\Repository\RpgPlotRepositoryInterface;
use Stu\StuTestCase;

class ShowWriteKnTest extends StuTestCase
{
    private MockInterface&RpgPlotRepositoryInterface $rpgPlotRepository;

    private ShowWriteKn $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->rpgPlotRepository = $this->mock(RpgPlotRepositoryInterface::class);

        $this->subject = new ShowWriteKn(
            $this->rpgPlotRepository
        );
    }

    public function testHandleRendersView(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $plot = $this->mock(RpgPlot::class);

        $userId = 666;

        $game->shouldReceive('getUser->getId')
            ->withNoArgs()
            ->once()
            ->andReturn($userId);
        $game->shouldReceive('setViewTemplate')
            ->with('html/communication/writeKn.twig')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with('comm.php', 'KommNet')
            ->once();
        $game->shouldReceive('appendNavigationPart')
            ->with(
                sprintf('comm.php?%s=1', ShowWriteKn::VIEW_IDENTIFIER),
                'Beitrag schreiben'
            )
            ->once();
        $game->shouldReceive('setPageTitle')
            ->with('Beitrag schreiben')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('ACTIVE_RPG_PLOTS', [$plot])
            ->once();

        $this->rpgPlotRepository->shouldReceive('getActiveByUser')
            ->with($userId)
            ->once()
            ->andReturn([$plot]);

        $this->subject->handle($game);
    }
}
