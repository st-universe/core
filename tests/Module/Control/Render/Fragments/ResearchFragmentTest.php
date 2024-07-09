<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Mockery\MockInterface;
use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalComponentFactoryInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Tal\StatusBarInterface;
use Stu\Orm\Entity\ResearchedInterface;
use Stu\Orm\Entity\ResearchInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;
use Stu\StuTestCase;

class ResearchFragmentTest extends StuTestCase
{
    /** @var ResearchedRepositoryInterface&MockInterface  */
    private MockInterface $researchedRepository;

    /** @var TalComponentFactoryInterface&MockInterface */
    private MockInterface $talComponentFactory;

    /** @var MockInterface&BuildingCommodityRepositoryInterface */
    private MockInterface $buildingCommodityRepository;

    private ResearchFragment $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->researchedRepository = $this->mock(ResearchedRepositoryInterface::class);
        $this->talComponentFactory = $this->mock(TalComponentFactoryInterface::class);
        $this->buildingCommodityRepository = $this->mock(BuildingCommodityRepositoryInterface::class);

        $this->subject = new ResearchFragment(
            $this->researchedRepository,
            $this->talComponentFactory,
            $this->buildingCommodityRepository
        );
    }

    public function testRenderRendersWithoutCurrentResearch(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);

        $this->researchedRepository->shouldReceive('getCurrentResearch')
            ->with($user)
            ->once()
            ->andReturn([]);

        $talPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH', null)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH_STATUS', '')
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('WAITING_RESEARCH', null)
            ->once();

        $this->subject->render($user, $talPage, $this->mock(GameControllerInterface::class));
    }

    public function testRenderRendersWithResearch(): void
    {
        $user = $this->mock(UserInterface::class);
        $talPage = $this->mock(TalPageInterface::class);
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

        $this->talComponentFactory->shouldReceive('createStatusBar')
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

        $talPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH', $currentResearch)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('CURRENT_RESEARCH_STATUS', $statusBar)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with('WAITING_RESEARCH', $waitingResearch)
            ->once();
        $talPage->shouldReceive('setVar')
            ->with(
                'CURRENT_RESEARCH_PRODUCTION_COMMODITY',
                $productionValue
            )
            ->once();

        $this->subject->render($user, $talPage, $this->mock(GameControllerInterface::class));
    }
}
