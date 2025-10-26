<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Stu\Orm\Entity\Alliance;

/**
 * Builds a graph node ("vertex") for an alliance
 */
final class RelationItemVertexBuilder implements RelationItemVertexBuilderInterface
{
    public function __construct(private AllianceDataToGraphAttributeConverterInterface $allianceDataToGraphAttributeConverter)
    {
    }

    #[\Override]
    public function build(
        Graph $graph,
        Alliance $alliance
    ): Vertex {
        $vertex = $graph->createVertex($alliance->getId());
        $vertex->setAttribute(
            'graphviz.label',
            $this->allianceDataToGraphAttributeConverter->convertName($alliance)
        );
        $vertex->setAttribute(
            'graphviz.fontcolor',
            '#9d9d9d'
        );
        $vertex->setAttribute(
            'graphviz.shape',
            'box'
        );
        $vertex->setAttribute(
            'graphviz.color',
            $this->allianceDataToGraphAttributeConverter->getFrameColor($alliance)
        );
        $vertex->setAttribute(
            'graphviz.style',
            'filled'
        );
        $vertex->setAttribute(
            'graphviz.fillcolor',
            $this->allianceDataToGraphAttributeConverter->getFillColor($alliance)
        );
        $vertex->setAttribute(
            'graphviz.fontname',
            'Arial'
        );
        $vertex->setAttribute(
            'graphviz.href',
            $this->allianceDataToGraphAttributeConverter->getUrl($alliance)
        );
        $vertex->setAttribute(
            'graphviz.target',
            '_blank'
        );

        return $vertex;
    }
}
