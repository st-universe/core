<?php

declare(strict_types=1);

namespace Stu\Component\GrapViz;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;

/**
 * Creates graphviz related items
 */
final class GraphVizFactory implements GraphVizFactoryInterface
{
    public function createGraph(): Graph
    {
        return new Graph();
    }

    public function createGraphViz(): GraphViz
    {
        return new GraphViz();
    }
}
