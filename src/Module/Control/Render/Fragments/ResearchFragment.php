<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Override;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

/**
 * Renders the research state view in the header
 */
final class ResearchFragment implements RenderFragmentInterface
{
    public function __construct(
        private ResearchedRepositoryInterface $researchedRepository,
        private StatusBarFactoryInterface $statusBarFactory,
        private TechlistRetrieverInterface $techlistRetriever,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {}

    #[Override]
    public function render(
        UserInterface $user,
        TwigPageInterface $page,
        GameControllerInterface $game
    ): void {
        $researchStatusBar = '';
        $currentResearch = $this->researchedRepository->getCurrentResearch($user);

        if ($currentResearch !== []) {
            $research = current($currentResearch)->getResearch();
            $researchPoints = $research->getPoints();

            $researchStatusBar = $this
                ->statusBarFactory
                ->createStatusBar()
                ->setColor(StatusBarColorEnum::STATUSBAR_BLUE)
                ->setLabel('Forschung')
                ->setMaxValue($researchPoints)
                ->setValue($researchPoints - current($currentResearch)->getActive())
                ->setSizeModifier(2);

            $page->setVar(
                'CURRENT_RESEARCH_PRODUCTION_COMMODITY',
                max(
                    0,
                    $this->buildingCommodityRepository->getProductionByCommodityAndUser(
                        $research->getCommodityId(),
                        $user
                    )
                )
            );
        }

        $researchList = $this->techlistRetriever->getResearchList($user);
        $hasResearchList = $researchList !== [];
        $hasCurrentResearch = $currentResearch !== [];

        $page->setVar('CURRENT_RESEARCH', current($currentResearch));
        $page->setVar('CURRENT_RESEARCH_STATUS', $researchStatusBar);
        $page->setVar('WAITING_RESEARCH', count($currentResearch) === 2 ? $currentResearch[1] : null);
        $page->setVar('RESEARCH_POSSIBLE', $hasResearchList || $hasCurrentResearch);
    }
}
