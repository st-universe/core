<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\Orm\Entity\Layer;
use Stu\StuTestCase;

class MapLayerRendererTest extends StuTestCase
{
    private MockInterface&Layer $layer;

    private MockInterface&EncodedMapInterface $encodedMap;

    private MockInterface&AbstractVisualPanel $panel;

    private LayerRendererInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->layer = mock(Layer::class);
        $this->encodedMap = mock(EncodedMapInterface::class);

        $this->panel = mock(AbstractVisualPanel::class);

        $this->subject = new MapLayerRenderer(
            $this->layer,
            $this->encodedMap
        );
    }

    public function testRenderExpectEncoded(): void
    {
        $mapData = new MapData(42, 42, 5);

        $this->layer->shouldReceive('isEncoded')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $this->encodedMap->shouldReceive('getEncodedMapPath')
            ->with(5, $this->layer)
            ->once()
            ->andReturn('ENCODED');

        $result = $this->subject->render($mapData, $this->panel);

        $expected = '<img src="/assets/map/ENCODED" style="z-index: 3; H+W; opacity:1;" />';

        $this->assertEquals($expected, $result);
    }

    public function testRenderExpectNotEncoded(): void
    {
        $mapData = new MapData(42, 42, 5);

        $this->layer->shouldReceive('isEncoded')
            ->withNoArgs()
            ->once()
            ->andReturn(false);
        $this->layer->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(99);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $expected = '<img src="/assets/map/99/5.png" style="z-index: 3; H+W; opacity:1;" />';

        $this->assertEquals($expected, $result);
    }
}
