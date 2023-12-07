<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\ResearchTree;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use Stu\Component\Research\ResearchEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

final class ShowResearchTree implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_RESEARCH_TREE';

    private ResearchRepositoryInterface $researchRepository;

    private ResearchDependencyRepositoryInterface $researchDependencyRepository;

    public function __construct(
        ResearchRepositoryInterface $researchRepository,
        ResearchDependencyRepositoryInterface $researchDependencyRepository
    ) {
        $this->researchRepository = $researchRepository;
        $this->researchDependencyRepository = $researchDependencyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        // only Admins can show it
        if (!$game->isAdmin()) {
            $game->addInformation(_('[b][color=#ff2626]Aktion nicht möglich, Spieler ist kein Admin![/color][/b]'));
            return;
        }

        $graph = new Graph();


        $research_list = $this->researchRepository->getForFaction(1);

        $dependencies = $this->researchDependencyRepository->getByMode([
            ResearchEnum::RESEARCH_MODE_REQUIRE,
            ResearchEnum::RESEARCH_MODE_REQUIRE_SOME
        ]);
        $excludes = $this->researchDependencyRepository->getByMode([ResearchEnum::RESEARCH_MODE_EXCLUDE]);

        $vertexes = [];

        foreach ($research_list as $research) {
            $vertex = $graph->createVertex($research->getId());
            $vertex->setAttribute('graphviz.label', $research->getName());
            $vertexes[$research->getId()] = $vertex;
        }

        foreach ($dependencies as $obj) {
            if (!array_key_exists($obj->getDependsOn(), $vertexes) || !array_key_exists($obj->getResearchId(), $vertexes)) {
                continue;
            }
            $vertexes[$obj->getDependsOn()]->createEdgeTo($vertexes[$obj->getResearchId()]);
        }
        foreach ($excludes as $obj) {
            if (!array_key_exists($obj->getDependsOn(), $vertexes) || !array_key_exists($obj->getResearchId(), $vertexes)) {
                continue;
            }
            $edge = $vertexes[$obj->getDependsOn()]->createEdgeTo($vertexes[$obj->getResearchId()]);
            $edge->setAttribute('graphviz.color', 'red');
        }

        $graphviz = new GraphViz();

        $game->appendNavigationPart(
            sprintf(
                '/admin/?%s=1',
                static::VIEW_IDENTIFIER
            ),
            _('Forschungsbaum')
        );
        $game->setTemplateFile('html/admin/researchTree.twig');
        $game->setPageTitle(_('Forschungsbaum'));
        $game->setTemplateVar('TREE', $graphviz->createImageHtml($graph));
    }
}
