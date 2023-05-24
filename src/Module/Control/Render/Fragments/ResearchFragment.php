<?php

declare(strict_types=1);

namespace Stu\Module\Control\Render\Fragments;

use Stu\Module\Tal\StatusBarColorEnum;
use Stu\Module\Tal\TalComponentFactoryInterface;
use Stu\Module\Tal\TalPageInterface;
use Stu\Module\Twig\TwigPageInterface;
use Stu\Orm\Entity\UserInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

/**
 * Renders the research state view in the header
 */
final class ResearchFragment implements RenderFragmentInterface
{
    private ResearchedRepositoryInterface $researchedRepository;
    private TalComponentFactoryInterface $talComponentFactory;

    private BuildingCommodityRepositoryInterface $buildingCommodityRepository;

    public function __construct(
        ResearchedRepositoryInterface $researchedRepository,
        TalComponentFactoryInterface $talComponentFactory,
        BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {
        $this->researchedRepository = $researchedRepository;
        $this->talComponentFactory = $talComponentFactory;
        $this->buildingCommodityRepository = $buildingCommodityRepository;
    }

    public function render(
        UserInterface $user,
        TalPageInterface|TwigPageInterface $page
    ): void {
        $researchStatusBar = '';
        $currentResearchReference = $this->researchedRepository->getCurrentResearch($user);

        if ($currentResearchReference !== null) {
            $research = $currentResearchReference->getResearch();
            $researchPoints = $research->getPoints();

            $researchStatusBar = $this
                ->talComponentFactory
                ->createTalStatusBar()
                ->setColor(StatusBarColorEnum::STATUSBAR_BLUE)
                ->setLabel('Forschung')
                ->setMaxValue($researchPoints)
                ->setValue($researchPoints - $currentResearchReference->getActive())
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

        $page->setVar('CURRENT_RESEARCH', $currentResearchReference);
        $page->setVar('CURRENT_RESEARCH_STATUS', $researchStatusBar);
    }
}
