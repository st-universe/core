<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Module\Template\StatusBarInterface;
use Stu\Orm\Entity\Researched;
use Stu\Orm\Entity\Research;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class ResearchComponentTest extends StuTestCase
{
    private ResearchedRepositoryInterface&MockInterface $researchedRepository;
    private StatusBarFactoryInterface&MockInterface $statusBarFactory;
    private MockInterface&BuildingCommodityRepositoryInterface $buildingCommodityRepository;
    private MockInterface&TechlistRetrieverInterface $techlistRetriever;

    private ResearchComponent $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->buildingCommodityRepository = $this->mock(BuildingCommodityRepositoryInterface::class);
        $this->techlistRetriever = $this->mock(TechlistRetrieverInterface::class);

        $this->subject = new ResearchComponent(
            $this->researchedRepository,
            $this->statusBarFactory,
            $this->techlistRetriever,
            $this->buildingCommodityRepository
        );
    }

    public function testRenderRendersWithoutCurrentResearch(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([]);

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setTemplateVar')
            ->with('CURRENT_RESEARCH', null)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CURRENT_RESEARCH_STATUS', '')
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('WAITING_RESEARCH', null)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('RESEARCH_POSSIBLE', false)
            ->once();

        $this->subject->setTemplateVariables($game);
    }

    public function testRenderRendersWithResearch(): void
    {
        $game = $this->mock(GameControllerInterface::class);
        $user = $this->mock(User::class);
        $currentResearch = $this->mock(Researched::class);
        $waitingResearch = $this->mock(Researched::class);
        $statusBar = $this->mock(StatusBarInterface::class);
        $research = $this->mock(Research::class);

        $points = 666;
        $alreadyResearchedPoints = 42;
        $researchCommodityId = 33;
        $productionValue = 123;

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([$currentResearch, $waitingResearch]);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([]);

        $research->shouldReceive('getPoints')
            ->withNoArgs()
            ->once()
            ->andReturn($points);
        $research->shouldReceive('getCommodityId')
            ->withNoArgs()
            ->once()
            ->andReturn($researchCommodityId);

        $this->buildingCommodityRepository->shouldReceive('getProductionByCommodityAndUser')
            ->with($researchCommodityId, $user)
            ->once()
            ->andReturn($productionValue);

        $currentResearch->shouldReceive('getResearch')
            ->withNoArgs()
            ->once()
            ->andReturn($research);
        $currentResearch->shouldReceive('getActive')
            ->withNoArgs()
            ->once()
            ->andReturn($alreadyResearchedPoints);

        $this->statusBarFactory->shouldReceive('createStatusBar')
            ->withNoArgs()
            ->once()
            ->andReturn($statusBar);

        $statusBar->shouldReceive('setColor')
            ->with(StatusBarColorEnum::BLUE)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setLabel')
            ->with('Forschung')
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setMaxValue')
            ->with($points)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setValue')
            ->with($points - $alreadyResearchedPoints)
            ->once()
            ->andReturnSelf();
        $statusBar->shouldReceive('setSizeModifier')
            ->with(2)
            ->once()
            ->andReturnSelf();

        $game->shouldReceive('getUser')
            ->withNoArgs()
            ->once()
            ->andReturn($user);
        $game->shouldReceive('setTemplateVar')
            ->with('CURRENT_RESEARCH', $currentResearch)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('CURRENT_RESEARCH_STATUS', $statusBar)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('WAITING_RESEARCH', $waitingResearch)
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with(
                'CURRENT_RESEARCH_PRODUCTION_COMMODITY',
                $productionValue
            )
            ->once();
        $game->shouldReceive('setTemplateVar')
            ->with('RESEARCH_POSSIBLE', true)
            ->once();

        $this->subject->setTemplateVariables($game);
    }
}
