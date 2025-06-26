<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Override;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\GrapViz\GraphVizFactoryInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;

/**
 * Renders the relations between alliances
 */
final class AllianceRelationRenderer implements AllianceRelationRendererInterface
{
    public function __construct(private GraphVizFactoryInterface $graphvizFactory, private RelationItemVertexBuilderInterface $relationItemVertexBuilder)
    {
    }

    #[Override]
    public function render(
        iterable $relationList,
        int $width,
        int $height,
        int $penWidth = 2,
        string $renderFormat = 'svg'
    ): string {
        $graph = $this->graphvizFactory->createGraph();
        $graph->setAttribute('graphviz.graph.charset', 'UTF-8');
        $graph->setAttribute('graphviz.graph.bgcolor', '#121220');
        $graph->setAttribute('graphviz.graph.tooltip', 'Diplomatische Beziehungen');
        $graph->setAttribute('graphviz.graph.ratio', 'compress');
        $graph->setAttribute('graphviz.graph.scale', 0.5);

        $vertexes = [];

        /** @var AllianceRelation $relation */
        foreach ($relationList as $relation) {
            $this->addAlliance($graph, $relation->getAlliance(), $vertexes);
            $this->addAlliance($graph, $relation->getOpponent(), $vertexes);

            $allianceId = $relation->getAllianceId();
            $opponentId = $relation->getOpponentId();

            $type = $relation->getType();

            $edge = $vertexes[$allianceId]->createEdge($vertexes[$opponentId]);
            $edge->setAttribute('graphviz.color', AllianceEnum::relationTypeToColor($type));
            $edge->setAttribute('graphviz.tooltip', AllianceEnum::relationTypeToDescription($type));
            $edge->setAttribute('graphviz.penwidth', $penWidth);
        }

        $graphviz = $this->graphvizFactory->createGraphViz();
        $graphviz->setFormat($renderFormat);

        return $graphviz->createImageHtml($graph);
    }

    /**
     * @param array<Vertex> $vertices
     */
    private function addAlliance(
        Graph $graph,
        Alliance $alliance,
        array &$vertices
    ): void {
        $allianceId = $alliance->getId();

        if (!array_key_exists($allianceId, $vertices)) {
            $vertices[$allianceId] = $this->relationItemVertexBuilder->build($graph, $alliance);
        }
    }
}
