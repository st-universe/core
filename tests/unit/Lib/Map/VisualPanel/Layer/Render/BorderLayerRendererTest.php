<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel\Layer\Render;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\System\Data\LssSystemData;
use Stu\Lib\Map\VisualPanel\AbstractVisualPanel;
use Stu\Lib\Map\VisualPanel\Layer\Data\BorderData;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\StuTestCase;

class BorderLayerRendererTest extends StuTestCase
{
    private MockInterface&AbstractVisualPanel $panel;

    #[Override]
    protected function setUp(): void
    {
        $this->panel = mock(AbstractVisualPanel::class);
    }

    public function testRenderExpectDefaultBorderColorWhenShipIsNull(): void
    {
        $borderData = $this->mock(BorderData::class);

        $subject = new BorderLayerRenderer(null, null);
        $result = $subject->render($borderData, $this->panel);

        $this->assertEquals('border:1px solid #2d2d2d;', $result);
    }

    public static function parameterExpectDefaultBorderColorWhenShipIsNotNullDataProvider(): array
    {
        return [
            [null],
            [false],
            [true, 11],
            [true, 10, 21],
            [true, 10, 20, 'border:1px solid #9b9b9b;'],
        ];
    }

    #[DataProvider('parameterExpectDefaultBorderColorWhenShipIsNotNullDataProvider')]
    public function testRenderExpectDefaultBorderColorWhenShipIsNotNull(
        ?bool $isOnShipLevel,
        ?int $shipX = null,
        ?int $shipY = null,
        string $expected = 'border:1px solid #2d2d2d;'
    ): void {
        $borderData = $this->mock(BorderData::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $lss = $this->mock(LssSystemData::class);

        $wrapper->shouldReceive('getLssSystemData')
            ->withNoArgs()
            ->andReturn($lss);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $lss->shouldReceive('getMode')
            ->withNoArgs()
            ->andReturn(SpacecraftLssModeEnum::NORMAL);

        if ($shipX !== null) {
            $ship->shouldReceive('getPosX')
                ->withNoArgs()
                ->andReturn($shipX);
        }
        if ($shipY !== null) {
            $ship->shouldReceive('getPosY')
                ->withNoArgs()
                ->andReturn($shipY);
        }

        $borderData->shouldReceive('getPosX')
            ->withNoArgs()
            ->andReturn(10);
        $borderData->shouldReceive('getPosY')
            ->withNoArgs()
            ->andReturn(20);

        $subject = new BorderLayerRenderer($wrapper, $isOnShipLevel);
        $result = $subject->render($borderData, $this->panel);

        $this->assertEquals($expected, $result);
    }

    public static function parameterExpectBorderOfUserAllyOrFactionDataProvider(): array
    {
        return [
            [null],
            ['FACTION', null, null, 'border:1px solid FACTION;'],
            [null, 'ALLY', null, 'border:1px solid ALLY;'],
            [null, null, 'USER', 'border:1px solid USER;'],
        ];
    }

    #[DataProvider('parameterExpectBorderOfUserAllyOrFactionDataProvider')]
    public function testRenderExpectBorderOfUserAllyOrFaction(
        ?string $factionColor,
        ?string $allyColor = null,
        ?string $userColor = null,
        string $expected = 'border:1px solid #2d2d2d;'
    ): void {
        $borderData = $this->mock(BorderData::class);
        $ship = $this->mock(ShipInterface::class);
        $wrapper = $this->mock(SpacecraftWrapperInterface::class);
        $lss = $this->mock(LssSystemData::class);

        $wrapper->shouldReceive('getLssSystemData')
            ->withNoArgs()
            ->once()
            ->andReturn($lss);
        $wrapper->shouldReceive('get')
            ->withNoArgs()
            ->andReturn($ship);

        $borderData->shouldReceive('getFactionColor')
            ->withNoArgs()
            ->andReturn($factionColor);
        $borderData->shouldReceive('getAllyColor')
            ->withNoArgs()
            ->andReturn($allyColor);
        $borderData->shouldReceive('getUserColor')
            ->withNoArgs()
            ->andReturn($userColor);

        $lss->shouldReceive('getMode')
            ->withNoArgs()
            ->andReturn(SpacecraftLssModeEnum::BORDER);

        $subject = new BorderLayerRenderer($wrapper, null);
        $result = $subject->render($borderData, $this->panel);

        $this->assertEquals($expected, $result);
    }
}
