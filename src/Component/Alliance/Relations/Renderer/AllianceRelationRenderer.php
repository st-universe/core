<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Stu\Component\Alliance\AllianceEnum;
use Stu\Component\GrapViz\GraphVizFactoryInterface;
use Stu\Orm\Entity\AllianceInterface;
use Stu\Orm\Entity\AllianceRelationInterface;

/**
 * Renders the relations between alliances
 */
final class AllianceRelationRenderer implements AllianceRelationRendererInterface
{
    /**
     * Fixed value, but depend on the user's dpi
     *
     * @var float
     */
    private const PIXEL_TO_INCH = 0.0104166667;

    private GraphVizFactoryInterface $graphvizFactory;

    private RelationItemVertexBuilderInterface $relationItemVertexBuilder;

    public function __construct(
        GraphVizFactoryInterface $graphvizFactory,
        RelationItemVertexBuilderInterface $relationItemVertexBuilder
    ) {
        $this->graphvizFactory = $graphvizFactory;
        $this->relationItemVertexBuilder = $relationItemVertexBuilder;
    }

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
        $graph->setAttribute(
            'graphviz.graph.size',
            sprintf('%02f,%02f', $width * self::PIXEL_TO_INCH, $height * self::PIXEL_TO_INCH)
        );
        $vertexes = [];

        /** @var AllianceRelationInterface $relation */
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
        AllianceInterface $alliance,
        array &$vertices
    ): void {
        $allianceId = $alliance->getId();

        if (!array_key_exists($allianceId, $vertices)) {
            $vertices[$allianceId] = $this->relationItemVertexBuilder->build($graph, $alliance);
        }
    }
}
