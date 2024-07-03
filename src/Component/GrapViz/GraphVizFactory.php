<?php

declare(strict_types=1);

namespace Stu\Component\GrapViz;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;
use Override;

/**
 * Creates graphviz related items
 */
final class GraphVizFactory implements GraphVizFactoryInterface
{
    #[Override]
    public function createGraph(): Graph
    {
        return new Graph();
    }

    #[Override]
    public function createGraphViz(): GraphViz
    {
        return new GraphViz();
    }
}
