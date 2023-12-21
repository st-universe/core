<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer;

use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\CellDataInterface;
use Stu\Lib\Map\VisualPanel\Layer\Render\LayerRendererInterface;
use Stu\StuTestCase;

class PanelLayerTest extends StuTestCase
{
    public function testRenderCellExpectRendering(): void
    {
        $cellData = mock(CellDataInterface::class);
        $renderer = mock(LayerRendererInterface::class);
        $panel = mock(AbstractVisualPanel::class);

        $cellData->shouldReceive('getPosX')
            ->withNoArgs()
            ->once()
            ->andReturn(1);
        $cellData->shouldReceive('getPosY')
            ->withNoArgs()
            ->once()
            ->andReturn(2);

        $renderer->shouldReceive('render')
            ->with($cellData, $panel)
            ->once()
            ->andReturn('RENDERED');

        $subject = new PanelLayer([$cellData], $renderer);

        $result = $subject->renderCell(1, 2, $panel);

        $this->assertEquals('RENDERED', $result);
    }
}
