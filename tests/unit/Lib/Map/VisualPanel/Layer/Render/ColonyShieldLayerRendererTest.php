<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\ColonyShieldData;
use Stu\StuTestCase;

class ColonyShieldLayerRendererTest extends StuTestCase
{
    /** @var MockInterface&AbstractVisualPanel */
    private MockInterface $panel;

    private LayerRendererInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);

        $this->subject = new ColonyShieldLayerRenderer();
    }

    public function testRenderExpectEmptyStringWhenShieldOff(): void
    {
        $mapData = $this->mock(ColonyShieldData::class);

        $mapData->shouldReceive('isShielded')
            ->withNoArgs()
            ->once()
            ->andReturn(false);

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectLayerStringWhenShieldOn(): void
    {
        $mapData = $this->mock(ColonyShieldData::class);

        $mapData->shouldReceive('isShielded')
            ->withNoArgs()
            ->once()
            ->andReturn(true);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $this->assertEquals(
            '<img src="/assets/planets/s1.png" class="lssColoShield" style="z-index: 4; H+W;" />',
            $result
        );
    }
}
