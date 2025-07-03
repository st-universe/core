<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\SpacecraftCountData;
use Stu\Orm\Entity\Ship;
use Stu\StuTestCase;

class SpacecraftCountLayerRendererTest extends StuTestCase
{
    private MockInterface&AbstractVisualPanel $panel;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);
    }

    public function testRenderExpectNothingWhenDubiousEffectButNoSignatures(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new SpacecraftCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectExclamationMarkWhenDubiousEffectAndSignatures(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(42);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->once()
            ->andReturn('FONTSIZE;');

        $subject = new SpacecraftCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('<div style="FONTSIZE; z-index: 7;" class="centered">!</div>', $result);
    }

    public function testRenderExpectNullWhenDisabled(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(false);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new SpacecraftCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectShipCount(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(false);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(1);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->once()
            ->andReturn('FONTSIZE;');

        $subject = new SpacecraftCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('<div style="FONTSIZE; z-index: 7;" class="centered">1</div>', $result);
    }

    public static function dubiousAndCloakedSignDataProvider(): array
    {
        return [
            [false, '?'],
            [true, '!'],
        ];
    }

    #[DataProvider('dubiousAndCloakedSignDataProvider')]
    public function testRenderExpectCloakedInfoWhenSet(
        bool $isDubious,
        string $expectedSign
    ): void {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn($isDubious);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $this->panel->shouldReceive('getFontSize')
            ->withNoArgs()
            ->once()
            ->andReturn('FONTSIZE;');

        $subject = new SpacecraftCountLayerRenderer(true, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals(sprintf('<div style="FONTSIZE; z-index: 7;" class="centered">%s</div>', $expectedSign), $result);
    }

    public function testRenderExpectNoCloakedInfoWhenNotSet(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(false);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new SpacecraftCountLayerRenderer(false, null);

        $result = $subject->render($mapData, $this->panel);

        $this->assertEquals('', $result);
    }

    public function testRenderExpectNoCloakedInfoWhenTachyonSystemOffline(): void
    {
        $mapData = $this->mock(SpacecraftCountData::class);
        $ship = $this->mock(Ship::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(false);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
            ->withNoArgs()
            ->andReturn(0);
        $mapData->shouldReceive('hasCloakedShips')
            ->withNoArgs()
            ->andReturn(true);

        $ship->shouldReceive('getTachyonState')
            ->withNoArgs()
            ->andReturn(false);

        $subject = new SpacecraftCountLayerRenderer(false, $ship);

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

    #[DataProvider('parameterDataProvider')]
    public function testRenderExpectCloakedInfoWhenTachyonInRange(
        bool $isStation,
        int $shipX,
        int $shipY,
        bool $isShowCloakedExpected
    ): void {
        $mapData = $this->mock(SpacecraftCountData::class);
        $ship = $this->mock(Ship::class);

        $mapData->shouldReceive('isDubious')
            ->withNoArgs()
            ->andReturn(false);
        $mapData->shouldReceive('isEnabled')
            ->withNoArgs()
            ->andReturn(true);
        $mapData->shouldReceive('getSpacecraftCount')
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
        $ship->shouldReceive('isStation')
            ->withNoArgs()
            ->andReturn($isStation);
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

        $subject = new SpacecraftCountLayerRenderer(false, $ship);

        $result = $subject->render($mapData, $this->panel);

        if ($isShowCloakedExpected) {

            $this->assertEquals('<div style="FONTSIZE; z-index: 7;" class="centered">?</div>', $result);
        } else {
            $this->assertEquals('', $result);
        }
    }
}
