<?php

declare(strict_types=1);

namespace Stu\Lib\Map\VisualPanel;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Map\VisualPanel\SignaturePanelEntry;
use Stu\Lib\Map\VisualPanel\VisualPanelEntryData;
use Stu\Orm\Entity\LayerInterface;
use Stu\StuTestCase;

class SignaturePanelEntryTest extends StuTestCase
{
    public static function provideGetCellDataForSystemMapData()
    {
        return [
            [1, 2, '0201'],
            [23, 2, '0223'],
            [7, 14, '1407'],
            [32, 16, '1632']
        ];
    }

    /**
     * @dataProvider provideGetCellDataForSystemMapData
     */
    public function testGetCellDataForSystemMap(
        int $x,
        int $y,
        string $expectedBackGroundId
    ): void {
        $data = mock(VisualPanelEntryData::class);
        $layer = mock(LayerInterface::class);
        $encodedMap = mock(EncodedMapInterface::class);

        $data->shouldReceive('getPosX')
            ->withNoArgs()
            ->andReturn($x);
        $data->shouldReceive('getPosY')
            ->withNoArgs()
            ->andReturn($y);
        $data->shouldReceive('getMapfieldType')
            ->withNoArgs()
            ->andReturn(42);
        $data->shouldReceive('getShieldState')
            ->withNoArgs()
            ->andReturn(true);
        $data->shouldReceive('getDirection1Count')
            ->withNoArgs()
            ->andReturn(1);
        $data->shouldReceive('getDirection2Count')
            ->withNoArgs()
            ->andReturn(2);
        $data->shouldReceive('getDirection3Count')
            ->withNoArgs()
            ->andReturn(3);
        $data->shouldReceive('getDirection4Count')
            ->withNoArgs()
            ->andReturn(4);
        $data->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(123);
        $data->shouldReceive('getSystemId')
            ->withNoArgs()
            ->andReturn(666);
        $data->shouldReceive('isSubspaceCodeAvailable')
            ->withNoArgs()
            ->andReturn(true);

        $subject = new SignaturePanelEntry($data, $layer, null);

        $result = $subject->getCellData();

        $this->assertEquals($expectedBackGroundId, $result->getSystemBackgroundId());
        $this->assertTrue($result->getColonyShieldState());
    }

    public static function provideGetCellDataForMapData()
    {
        return [
            [false, '5/42.png'],
            [true, 'ENCODED']
        ];
    }

    /**
     * @dataProvider provideGetCellDataForMapData
     */
    public function testGetCellDataForMap(
        bool $isEncoded,
        string $expectedMapGraphicPath
    ): void {
        $data = mock(VisualPanelEntryData::class);
        $layer = mock(LayerInterface::class);
        $encodedMap = mock(EncodedMapInterface::class);

        $data->shouldReceive('getMapfieldType')
            ->withNoArgs()
            ->andReturn(42);
        $data->shouldReceive('getShipCount')
            ->withNoArgs()
            ->andReturn(123);
        $data->shouldReceive('getSystemId')
            ->withNoArgs()
            ->andReturn(null);
        $data->shouldReceive('isSubspaceCodeAvailable')
            ->withNoArgs()
            ->andReturn(false);

        $layer->shouldReceive('isEncoded')
            ->withNoArgs()
            ->andReturn($isEncoded);
        if ($isEncoded) {
            $encodedMap->shouldReceive('getEncodedMapPath')
                ->with(42, $layer)
                ->andReturn("ENCODED");
        } else {
            $layer->shouldReceive('getId')
                ->withNoArgs()
                ->andReturn(5);
        }

        $subject = new SignaturePanelEntry($data, $layer, $encodedMap);

        $result = $subject->getCellData();

        $this->assertEquals($expectedMapGraphicPath, $result->getMapGraphicPath());
    }
}
