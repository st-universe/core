<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Edge\Undirected;
use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Graphp\GraphViz\GraphViz;
use Mockery\MockInterface;
use Stu\Component\Alliance\Enum\AllianceRelationTypeEnum;
use Stu\Component\GrapViz\GraphVizFactoryInterface;
use Stu\Orm\Entity\Alliance;
use Stu\Orm\Entity\AllianceRelation;
use Stu\StuTestCase;

class AllianceRelationRendererTest extends StuTestCase
{
    private MockInterface&GraphVizFactoryInterface $graphVizFactory;

    private MockInterface&RelationItemVertexBuilderInterface $relationItemVertexBuilder;

    private AllianceRelationRenderer $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->graphVizFactory = $this->mock(GraphVizFactoryInterface::class);
        $this->relationItemVertexBuilder = $this->mock(RelationItemVertexBuilderInterface::class);

        $this->subject = new AllianceRelationRenderer(
            $this->graphVizFactory,
            $this->relationItemVertexBuilder
        );
    }

    public function testRenderRenders(): void
    {
        $relation = $this->mock(AllianceRelation::class);
        $alliance = $this->mock(Alliance::class);
        $opponent = $this->mock(Alliance::class);
        $graph = $this->mock(Graph::class);
        $graphViz = $this->mock(GraphViz::class);
        $vertex1 = $this->mock(Vertex::class);
        $vertex2 = $this->mock(Vertex::class);
        $edge = $this->mock(Undirected::class);

        $width = 666;
        $height = 42;
        $allianceId = 666;
        $opponentId = 42;
        $result = 'some-result';
        $relationType = AllianceRelationTypeEnum::ALLIED;

        $this->graphVizFactory->shouldReceive('createGraphViz')
            ->withNoArgs()
            ->once()
            ->andReturn($graphViz);
        $this->graphVizFactory->shouldReceive('createGraph')
            ->withNoArgs()
            ->once()
            ->andReturn($graph);

        $relation->shouldReceive('getAlliance')
            ->withNoArgs()
            ->once()
            ->andReturn($alliance);
        $relation->shouldReceive('getOpponent')
            ->withNoArgs()
            ->once()
            ->andReturn($opponent);
        $relation->shouldReceive('getType')
            ->withNoArgs()
            ->once()
            ->andReturn($relationType);
        $relation->shouldReceive('getAllianceId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);
        $relation->shouldReceive('getOpponentId')
            ->withNoArgs()
            ->once()
            ->andReturn($opponentId);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);

        $opponent->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($opponentId);

        $vertex1->shouldReceive('createEdge')
            ->with($vertex2)
            ->once()
            ->andReturn($edge);

        $edge->shouldReceive('setAttribute')
            ->with('graphviz.color', '#005183')
            ->once();
        $edge->shouldReceive('setAttribute')
            ->with('graphviz.tooltip', $relationType->getDescription())
            ->once();
        $edge->shouldReceive('setAttribute')
            ->with('graphviz.penwidth', 2)
            ->once();

        $graphViz->shouldReceive('setFormat')
            ->with('svg')
            ->once();
        $graphViz->shouldReceive('createImageHtml')
            ->with($graph)
            ->once()
            ->andReturn($result);

        $graph->shouldReceive('setAttribute')
            ->with('graphviz.graph.charset', 'UTF-8')
            ->once();
        $graph->shouldReceive('setAttribute')
            ->with('graphviz.graph.bgcolor', '#121220')
            ->once();
        $graph->shouldReceive('setAttribute')
            ->with('graphviz.graph.tooltip', 'Diplomatische Beziehungen')
            ->once();
        $graph->shouldReceive('setAttribute')
            ->with('graphviz.graph.ratio', 'compress')
            ->once();
        $graph->shouldReceive('setAttribute')
            ->with('graphviz.graph.scale', 0.5)
            ->once();

        $this->relationItemVertexBuilder->shouldReceive('build')
            ->with($graph, $alliance)
            ->once()
            ->andReturn($vertex1);
        $this->relationItemVertexBuilder->shouldReceive('build')
            ->with($graph, $opponent)
            ->once()
            ->andReturn($vertex2);

        static::assertSame(
            $result,
            $this->subject->render([$relation], $width, $height)
        );
    }
}
