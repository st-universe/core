<?php

declare(strict_types=1);

namespace Stu\Module\Game\Component;

use Stu\Lib\Component\ComponentInterface;
use Stu\Module\Template\TemplateInterface;
use Stu\Orm\Entity\User;
use Stu\Module\Research\TechlistRetrieverInterface;
use Stu\Module\Template\StatusBarColorEnum;
use Stu\Module\Template\StatusBarFactoryInterface;
use Stu\Orm\Repository\BuildingCommodityRepositoryInterface;
use Stu\Orm\Repository\ResearchedRepositoryInterface;

/**
 * Renders the research state view in the header
 */
final class ResearchComponent implements ComponentInterface
{
    public function __construct(
        private ResearchedRepositoryInterface $researchedRepository,
        private StatusBarFactoryInterface $statusBarFactory,
        private TechlistRetrieverInterface $techlistRetriever,
        private BuildingCommodityRepositoryInterface $buildingCommodityRepository
    ) {}

    #[\Override]
    public function setTemplateVariables(User $user, TemplateInterface $template): void
    {
        $researchStatusBar = '';
        $currentResearch = $this->researchedRepository->getCurrentResearch($user);

        if ($currentResearch !== []) {
            $research = current($currentResearch)->getResearch();
            $researchPoints = $research->getPoints();

            $researchStatusBar = $this
                ->statusBarFactory
                ->createStatusBar()
                ->setColor(StatusBarColorEnum::BLUE)
                ->setLabel('Forschung')
                ->setMaxValue($researchPoints)
                ->setValue($researchPoints - current($currentResearch)->getActive())
                ->setSizeModifier(2);

            $template->setTemplateVar(
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

        $template->setTemplateVar('CURRENT_RESEARCH', current($currentResearch));
        $template->setTemplateVar('CURRENT_RESEARCH_STATUS', $researchStatusBar);
        $template->setTemplateVar('WAITING_RESEARCH', count($currentResearch) === 2 ? $currentResearch[1] : null);
        $template->setTemplateVar('RESEARCH_POSSIBLE', $hasResearchList || $hasCurrentResearch);
    }
}
