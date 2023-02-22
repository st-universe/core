<?php

declare(strict_types=1);

namespace Stu\Component\GrapViz;

use Fhaculty\Graph\Graph;
use Graphp\GraphViz\GraphViz;

interface GraphVizFactoryInterface
{
    public function createGraph(): Graph;

    public function createGraphViz(): GraphViz;
}
