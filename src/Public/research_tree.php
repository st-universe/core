<?php

use Stu\Component\Research\ResearchEnum;
use Stu\Orm\Repository\ResearchDependencyRepositoryInterface;
use Stu\Orm\Repository\ResearchRepositoryInterface;

require_once __DIR__ . '/../../Config/Bootstrap.php';

$graph = new Fhaculty\Graph\Graph();

$researchRepository = $container->get(ResearchRepositoryInterface::class);
$researchDependencyRepository = $container->get(ResearchDependencyRepositoryInterface::class);

$research_list = $researchRepository->getForFaction(1);

$dependencies = $researchDependencyRepository->getByMode([ResearchEnum::RESEARCH_MODE_REQUIRE,
    ResearchEnum::RESEARCH_MODE_REQUIRE_SOME
]);
$excludes = $researchDependencyRepository->getByMode([ResearchEnum::RESEARCH_MODE_EXCLUDE]);

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

$graphviz = new Graphp\GraphViz\GraphViz();
$graphviz->display($graph);
