<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\SubspaceData;
use Stu\StuTestCase;

class SubspaceLayerRendererTest extends StuTestCase
{
    /** @var MockInterface|AbstractVisualPanel */
    private MockInterface $panel;

    private LayerRendererInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);

        $this->subject = new SubspaceLayerRenderer();
    }

    public function testRenderExpectEmptyStringIfNoCodeAvailable(): void
    {
        $mapData = mock(SubspaceData::class);

        $mapData->shouldReceive('getDirection1Count')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $mapData->shouldReceive('getDirection2Count')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $mapData->shouldReceive('getDirection3Count')
            ->withNoArgs()
            ->once()
            ->andReturn(0);
        $mapData->shouldReceive('getDirection4Count')
            ->withNoArgs()
            ->once()
            ->andReturn(0);

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectRenderWhenCodeAvailable1(): void
    {
        $mapData = mock(SubspaceData::class);

        $mapData->shouldReceive('getDirection1Count')
            ->withNoArgs()
            ->andReturn(1);
        $mapData->shouldReceive('getDirection2Count')
            ->withNoArgs()
            ->andReturn(5);
        $mapData->shouldReceive('getDirection3Count')
            ->withNoArgs()
            ->andReturn(10);
        $mapData->shouldReceive('getDirection4Count')
            ->withNoArgs()
            ->andReturn(20);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals(
            '<img src="/assets/subspace/generated/1234.png" class="lssSubspaceOverShield"
                style="z-index: 5; H+W;" />',
            $result
        );
    }

    public function testRenderExpectRenderWhenCodeAvailable2(): void
    {
        $mapData = mock(SubspaceData::class);

        $mapData->shouldReceive('getDirection1Count')
            ->withNoArgs()
            ->andReturn(2);
        $mapData->shouldReceive('getDirection2Count')
            ->withNoArgs()
            ->andReturn(6);
        $mapData->shouldReceive('getDirection3Count')
            ->withNoArgs()
            ->andReturn(11);
        $mapData->shouldReceive('getDirection4Count')
            ->withNoArgs()
            ->andReturn(21);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals(
            '<img src="/assets/subspace/generated/2345.png" class="lssSubspaceOverShield"
                style="z-index: 5; H+W;" />',
            $result
        );
    }
}
