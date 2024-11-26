<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Module\Template\StatusBarInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class ResearchFragmentTest extends StuTestCase
{
    /** @var ResearchedRepositoryInterface&MockInterface  */
    private $researchedRepository;

    /** @var StatusBarFactoryInterface&MockInterface */
    private $statusBarFactory;

    /** @var MockInterface&BuildingCommodityRepositoryInterface */
    private $buildingCommodityRepository;

    /** @var MockInterface&TechlistRetrieverInterface */
    private $techlistRetriever;

    private ResearchFragment $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->statusBarFactory = $this->mock(StatusBarFactoryInterface::class);
        $this->buildingCommodityRepository = $this->mock(BuildingCommodityRepositoryInterface::class);
        $this->techlistRetriever = $this->mock(TechlistRetrieverInterface::class);

        $this->subject = new ResearchFragment(
            $this->researchedRepository,
            $this->statusBarFactory,
            $this->techlistRetriever,
            $this->buildingCommodityRepository
        );
    }

    public function testRenderRendersWithoutCurrentResearch(): void
    {
        $user = $this->mock(UserInterface::class);
        $twigPage = $this->mock(TwigPageInterface::class);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $this->techlistRetriever->shouldReceive('getResearchList')
            ->with($user)
            ->once()
            ->andReturn([]);

        $twigPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH', null)
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH_STATUS', '')
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('WAITING_RESEARCH', null)
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('RESEARCH_POSSIBLE', false)
            ->once();

        $this->subject->render($user, $twigPage, $this->mock(GameControllerInterface::class));
    }

    public function testRenderRendersWithResearch(): void
    {
        $user = $this->mock(UserInterface::class);
        $twigPage = $this->mock(TwigPageInterface::class);
        $currentResearch = $this->mock(ResearchedInterface::class);
        $waitingResearch = $this->mock(ResearchedInterface::class);
        $statusBar = $this->mock(StatusBarInterface::class);
        $research = $this->mock(ResearchInterface::class);

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
            ->with(StatusBarColorEnum::STATUSBAR_BLUE)
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

        $twigPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH', $currentResearch)
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH_STATUS', $statusBar)
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('WAITING_RESEARCH', $waitingResearch)
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with(
                'CURRENT_RESEARCH_PRODUCTION_COMMODITY',
                $productionValue
            )
            ->once();
        $twigPage->shouldReceive('setVar')
            ->with('RESEARCH_POSSIBLE', true)
            ->once();

        $this->subject->render($user, $twigPage, $this->mock(GameControllerInterface::class));
    }
}
