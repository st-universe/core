<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\MapData;
use Stu\StuTestCase;
use tidy;

class SystemLayerRendererTest extends StuTestCase
{
    private LayerRendererInterface $subject;

    /** @var MockInterface|AbstractVisualPanel */
    private MockInterface $panel;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);

        $this->subject = new SystemLayerRenderer();
    }

    public static function provideGetBackgroundImageData(): array
    {
        return [
            [1, 2, '0201'],
            [23, 2, '0223'],
            [7, 14, '1407'],
            [32, 16, '1632']
        ];
    }

    #[DataProvider('provideGetBackgroundImageData')]
    public function testRenderExpectBackgroundOnly(
        int $x,
        int $y,
        string $expectedBackGroundId
    ): void {
        $mapData = new MapData($x, $y, 1);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->once()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $expected = sprintf(
            '<img src="/assets/map/starmap/%s.png"
                        style="z-index: 1; H+W; opacity:1;" />',
            $expectedBackGroundId
        );

        $this->assertXmlStringEqualsXmlString($expected, $result);
    }

    public function testRenderExpectBackgroundPlusFieldImage(): void
    {
        $mapData = new MapData(1, 2, 3);

        $this->panel->shouldReceive('getHeightAndWidth')
            ->withNoArgs()
            ->twice()
            ->andReturn('H+W;');

        $result = $this->subject->render($mapData, $this->panel);

        $expected = '<img src="/assets/map/starmap/0201.png"
                        style="z-index: 1; H+W; opacity:1;" />
            <img src="/assets/map/3.png" class="lssSubspaceOverShield"
                        style="z-index: 2; H+W; opacity:2;" />';

        $this->assertEquals(
            preg_replace('/\s+/', '', $expected),
            preg_replace('/\s+/', '', $result)
        );
    }
}
