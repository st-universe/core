<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Mockery\MockInterface;
use Stu\Lib\Map\VisualPanel\Layer\PanelLayers;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\StuTestCase;

class SignaturePanelEntryTest extends StuTestCase
{
    public function testGetRenderedCellLayers(): void
    {
        /** @var MockInterface */
        $layers = mock(PanelLayers::class);

        $layers->shouldReceive('getRenderedCellLayers')
            ->with(1, 2)
            ->once()
            ->andReturn(['A', 'B']);

        $subject = new SignaturePanelEntry(1, 2, $layers);

        $result = $subject->getRenderedCellLayers();

        $this->assertEquals(['A', 'B'], $result);
    }

    public function testGetBorder(): void
    {
        /** @var MockInterface */
        $layers = mock(PanelLayers::class);

        $layers->shouldReceive('getCellBorder')
            ->with(1, 2)
            ->once()
            ->andReturn('BORDER');

        $subject = new SignaturePanelEntry(1, 2, $layers);

        $result = $subject->getBorder();

        $this->assertEquals('BORDER', $result);
    }

    public function testGetCssClass(): void
    {
        /** @var MockInterface */
        $layers = mock(PanelLayers::class);

        $subject = new SignaturePanelEntry(1, 2, $layers);

        $result = $subject->getCssClass();

        $this->assertEquals('lss', $result);
    }
}
