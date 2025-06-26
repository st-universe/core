<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Stu\Orm\Entity\Alliance;

interface RelationItemVertexBuilderInterface
{
    /**
     * Returns a configured graph node ("vertex")
     */
    public function build(Graph $graph, Alliance $alliance): Vertex;
}
