<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ResearchTree;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use Override;
use request;
use Stu\Module\Commodity\CommodityTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\ResearchDependency;
use Stu\Orm\Entity\Research;
use Stu\Orm\Repository\FactionRepositoryInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;

final class ShowResearchTree implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_RESEARCH_TREE';

    public function __construct(private FactionRepositoryInterface $factionRepository, private ResearchDependencyRepositoryInterface $researchDependencyRepository)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        // only Admins can show it
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $graph = new Graph();

        $factionId = request::postIntFatal('factionid');
        $faction = $this->factionRepository->find($factionId);
        if ($faction === null) {
            $game->addInformationf('Faction with following id does not exist: %d', $factionId);
            return;
        }

        $vertexes = [];
        $points = 0;

        $startResearch = $faction->getStartResearch();
        if ($startResearch !== null) {
            $this->addResearch($startResearch, $graph, $vertexes, $points);
        }

        $graphviz = new GraphViz();

        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                self::VIEW_IDENTIFIER
            ),
            _('Forschungsbaum')
        );
        $game->setTemplateFile('html/admin/researchTree.twig');
        $game->setPageTitle(_('Forschungsbaum'));
        $game->setTemplateVar('POINTS', $points);
        $game->setTemplateVar('TREE', $graphviz->createImageHtml($graph));
    }

    /** @param array<Vertex> $vertexes */
    private function addResearch(Research $research, Graph $graph, array &$vertexes, int &$points): void
    {
        $researchId = $research->getId();
        if (array_key_exists($research->getId(), $vertexes)) {
            return;
        }

        // create node
        $vertex = $graph->createVertex($researchId);
        $vertex->setAttribute('graphviz.label', $research->getName());
        $vertexes[$researchId] = $vertex;

        // compute dependencies
        foreach ($this->researchDependencyRepository->getByDependingResearch($researchId) as $dependency) {
            $this->addResearch($dependency->getResearch(), $graph, $vertexes, $points);
            $this->addEdge($dependency, $vertexes);
        }

        $commodityId = $research->getCommodityId();

        if ($commodityId === CommodityTypeEnum::COMMODITY_RESEARCH_LVL1) {
            $points += $research->getPoints();
        }
        if ($commodityId === CommodityTypeEnum::COMMODITY_RESEARCH_LVL2) {
            $points += $research->getPoints() * 2;
        }
        if ($commodityId === CommodityTypeEnum::COMMODITY_RESEARCH_LVL3) {
            $points += $research->getPoints() * 3;
        }
        if (in_array($commodityId, CommodityTypeEnum::COMMODITY_RESEARCH_LVL4)) {
            $points += $research->getPoints() * 4;
        }
    }

    /** @param array<Vertex> $vertexes */
    private function addEdge(ResearchDependency $researchDependency, array $vertexes): void
    {
        $edge = $vertexes[$researchDependency->getDependsOn()]
            ->createEdgeTo($vertexes[$researchDependency->getResearchId()]);

        $color = $researchDependency->getMode()->getTechtreeEdgeColor();
        if ($color !== null) {
            $edge->setAttribute('graphviz.color', $color);
        }
    }
}
