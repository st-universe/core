<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Lib\Gui\Component;

use Mockery;
use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Orm\Entity\ColonyInterface;
use Stu\Orm\Repository\PlanetFieldRepositoryInterface;
use Stu\StuTestCase;

class EpsBarProviderTest extends StuTestCase
{
    private MockInterface&PlanetFieldRepositoryInterface $planetFieldRepository;

    private MockInterface&ColonyInterface $host;
    private MockInterface&GameControllerInterface $game;

    private PlanetFieldHostComponentInterface $subject;

    #[Override]
    public function setUp(): void
    {
        $this->planetFieldRepository = Mockery::mock(PlanetFieldRepositoryInterface::class);

        $this->host = $this->mock(ColonyInterface::class);
        $this->game = $this->mock(GameControllerInterface::class);

        $this->subject = new EpsBarProvider(
            $this->planetFieldRepository
        );
    }

    public function testSetTemplateVariables(): void
    {
        $this->host->shouldReceive('getEps')
            ->withNoArgs()
            ->andReturn(80);
        $this->host->shouldReceive('getMaxEps')
            ->withNoArgs()
            ->andReturn(100);

        $this->planetFieldRepository->shouldReceive('getEnergyProductionByHost')
            ->with($this->host)
            ->once()
            ->andReturn(10);

        $this->game->shouldReceive('setTemplateVar')
            ->with(
                'EPS_STATUS_BAR',
                '<img src="/assets/bars/balken.png" style="background-color: #aaaa00;height: 12px; width: 288px;" title="Energieproduktion" /><img src="/assets/bars/balken.png" style="background-color: #00aa00;height: 12px; width: 36px;" title="Energieproduktion" /><img src="/assets/bars/balken.png" style="background-color: #777777;height: 12px; width: 36px;" title="Energieproduktion" />'
            )
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('EPS_PRODUCTION', '10')
            ->once();
        $this->game->shouldReceive('setTemplateVar')
            ->with('EPS_BAR_TITLE_STRING', 'Energie: 80/100 (+10/Runde = 90)')
            ->once();

        $this->subject->setTemplateVariables(
            $this->host,
            $this->game
        );
    }
}
