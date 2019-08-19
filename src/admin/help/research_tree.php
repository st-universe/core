<?php

require_once __DIR__.'/../../inc/config.inc.php';

$graph = new Fhaculty\Graph\Graph();

$research_list = \Research::getList(2);
$dependencies = \ResearchDependency::getList();
$excludes = \ResearchDependency::getListExcludes();

$vertexes = [];

foreach ($research_list as $research) {
    $vertex = $graph->createVertex($research->getId());
    $vertex->setAttribute('graphviz.label', $research->getName());
    $vertexes[$research->getId()] = $vertex;
}

foreach ($dependencies as $research_id => $dependency) {
    foreach ($dependency as $obj) {
        if (!array_key_exists($obj->getDependOn(), $vertexes) || !array_key_exists($research_id, $vertexes)) {
            continue;
        }
        $vertexes[$obj->getDependOn()]->createEdgeTo($vertexes[$research_id]);
    }
}
foreach ($excludes as $depend_on => $dependency) {
    foreach ($dependency as $obj) {
        if (!array_key_exists($obj->getDependOn(), $vertexes) || !array_key_exists($depend_on, $vertexes)) {
            continue;
        }
        $edge = $vertexes[$depend_on]->createEdgeTo($vertexes[$obj->getResearchId()]);
        $edge->setAttribute('graphviz.color', 'red');
    }
}

$graphviz = new Graphp\GraphViz\GraphViz();
$graphviz->display($graph);
