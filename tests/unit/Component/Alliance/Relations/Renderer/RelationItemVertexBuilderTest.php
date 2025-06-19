<?php

declare(strict_types=1);

namespace Stu\Component\Alliance\Relations\Renderer;

use Fhaculty\Graph\Graph;
use Fhaculty\Graph\Vertex;
use Mockery\MockInterface;
use Override;
use Stu\Orm\Entity\AllianceInterface;
use Stu\StuTestCase;

class RelationItemVertexBuilderTest extends StuTestCase
{
    private MockInterface&AllianceDataToGraphAttributeConverterInterface $allianceDataToGraphAttributeConverter;

    private RelationItemVertexBuilder $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->allianceDataToGraphAttributeConverter = $this->mock(AllianceDataToGraphAttributeConverterInterface::class);

        $this->subject = new RelationItemVertexBuilder(
            $this->allianceDataToGraphAttributeConverter
        );
    }

    public function testBuildReturnsVertex(): void
    {
        $graph = $this->mock(Graph::class);
        $alliance = $this->mock(AllianceInterface::class);
        $vertex = $this->mock(Vertex::class);

        $allianceId = 666;
        $name = 'some-name';
        $frameColor = 'some-color';
        $fillColor = 'some-fill-color';
        $url = 'some-url';

        $graph->shouldReceive('createVertex')
            ->with($allianceId)
            ->once()
            ->andReturn($vertex);

        $alliance->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn($allianceId);

        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.label',
                $name
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.fontcolor',
                '#9d9d9d'
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.shape',
                'box'
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.color',
                $frameColor
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.style',
                'filled'
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.fillcolor',
                $fillColor
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.fontname',
                'Arial'
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.href',
                $url
            )
            ->once();
        $vertex->shouldReceive('setAttribute')
            ->with(
                'graphviz.target',
                '_blank'
            )
            ->once();

        $this->allianceDataToGraphAttributeConverter->shouldReceive('convertName')
            ->with($alliance)
            ->once()
            ->andReturn($name);
        $this->allianceDataToGraphAttributeConverter->shouldReceive('getFrameColor')
            ->with($alliance)
            ->once()
            ->andReturn($frameColor);
        $this->allianceDataToGraphAttributeConverter->shouldReceive('getFillColor')
            ->with($alliance)
            ->once()
            ->andReturn($fillColor);
        $this->allianceDataToGraphAttributeConverter->shouldReceive('getUrl')
            ->with($alliance)
            ->once()
            ->andReturn($url);

        static::assertSame(
            $vertex,
            $this->subject->build($graph, $alliance)
        );
    }
}
