<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\ShipCountData;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class ShipCountLayerRendererTest extends StuTestCase
{
    /** @var MockInterface|AbstractVisualPanel */
    private MockInterface $panel;

    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);
    }

    public function testRenderExpectShipCount(): void
    {
        $mapData = mock(ShipCountData::class);

        $mapData->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(1);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->once()
            ->andReturn('FONTSIZE;');

        $subject = new ShipCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('<div style="FONTSIZE; z-index: 6;" class="centered">1</div>', $result);
    }

    public function testRenderExpectCloakedInfoWhenSet(): void
    {
        $mapData = mock(ShipCountData::class);

        $mapData->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->once()
            ->andReturn('FONTSIZE;');

        $subject = new ShipCountLayerRenderer(true, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('<div style="FONTSIZE; z-index: 6;" class="centered">?</div>', $result);
    }

    public function testRenderExpectNoCloakedInfoWhenNotSet(): void
    {
        $mapData = mock(ShipCountData::class);

        $mapData->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new ShipCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectNoCloakedInfoWhenTachyonSystemOffline(): void
    {
        $mapData = mock(ShipCountData::class);
        $ship = mock(ShipInterface::class);

        $mapData->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $ship->shouldReceive('getTachyonState')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new ShipCountLayerRenderer(false, $ship);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public static function parameterDataProvider(): array
    {
        return [
            // border for non-base
            [false, 7, 20, true],
            [false, 7, 19, true],
            [false, 7, 18, true],
            [false, 7, 17, true],
            [false, 8, 17, true],
            [false, 9, 17, true],
            [false, 10, 17, true],
            [false, 11, 17, true],
            [false, 12, 17, true],
            [false, 13, 17, true],
            [false, 13, 18, true],
            [false, 13, 19, true],
            [false, 13, 20, true],
            [false, 13, 21, true],
            [false, 13, 22, true],
            [false, 13, 23, true],
            [false, 12, 23, true],
            [false, 11, 23, true],
            [false, 10, 23, true],
            [false, 9, 23, true],
            [false, 8, 23, true],
            [false, 7, 23, true],
            [false, 7, 22, true],
            [false, 7, 21, true],

            //edge cases for non-base
            [false, 6, 20, false],
            [false, 10, 24, false],
            [false, 10, 16, false],
            [false, 14, 20, false],

            //cases for base
            [true, 3, 20, true],
            [true, 10, 27, true],
            [true, 10, 13, true],
            [true, 17, 20, true],
            [true, 2, 20, false],
            [true, 10, 28, false],
            [true, 10, 12, false],
            [true, 18, 20, false]
        ];
    }

    /**
     * @dataProvider parameterDataProvider
     */
    public function testRenderExpectNoCloakedInfoWhenTachyonInRange(
        bool $isBase,
        int $shipX,
        int $shipY,
        bool $isShowCloakedExpected
    ): void {
        $mapData = mock(ShipCountData::class);
        $ship = mock(ShipInterface::class);

        $mapData->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getPosX')
            ->withNoArgs()
            ->andReturn(10);
        $mapData->shouldReceive('getPosY')
            ->withNoArgs()
            ->andReturn(20);

        $ship->shouldReceive('getTachyonState')
            ->withNoArgs()
            ->andReturn(true);
        $ship->shouldReceive('isBase')
            ->withNoArgs()
            ->andReturn($isBase);
        $ship->shouldReceive('getPosX')
            ->withNoArgs()
            ->andReturn($shipX);
        $ship->shouldReceive('getPosY')
            ->withNoArgs()
            ->andReturn($shipY);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->zeroOrMoreTimes()
            ->andReturn('FONTSIZE;');

        $subject = new ShipCountLayerRenderer(false, $ship);

        $result = $subject->render($mapData, $this->panel);

        if ($isShowCloakedExpected) {

            $this->assertEquals('<div style="FONTSIZE; z-index: 6;" class="centered">?</div>', $result);
        } else {
            $this->assertEquals('', $result);
        }
    }
}
