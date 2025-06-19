<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\AnomalyData;
use Stu\StuTestCase;

class AnomalyLayerRendererTest extends StuTestCase
{
    private LayerRendererInterface $subject;

    private MockInterface&AbstractVisualPanel $panel;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);

        $this->subject = new AnomalyLayerRenderer();
    }

    public function testRenderExpectEmptyStringIfNoAnomalies(): void
    {
        $mapData = new AnomalyData(1, 2, '');

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectLayersIfAnomaliesPresent(): void
    {
        $mapData = new AnomalyData(1, 2, '2,42');

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->twice()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $expected = '<img src="/assets/map/anomalies/2.png" class="visualPanelLayer"
                        style="z-index: 8; H+W; opacity: 0.8;" />
            <img src="/assets/map/anomalies/42.png" class="visualPanelLayer"
                        style="z-index: 9; H+W; opacity: 0.8;" />';

        $this->assertEquals(
            preg_replace('/\s+/', '', $expected),
            preg_replace('/\s+/', '', $result)
        );
    }
}
