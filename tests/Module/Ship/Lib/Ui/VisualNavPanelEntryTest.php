<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\Component\Map\EncodedMapInterface;
use Stu\Orm\Entity\LayerInterface;
use Stu\StuTestCase;

class VisualNavPanelEntryTest extends StuTestCase
{
    public static function provideGetSystemBackgroundIdData()
    {
        return [
            [1, 2, '0201', false, '5/42.png'],
            [23, 2, '0223', true, 'ENCODED'],
            [7, 14, '1407', true, 'ENCODED'],
            [32, 16, '1632', true, 'ENCODED'],
        ];
    }

    /**
     * @dataProvider provideGetSystemBackgroundIdData
     */
    public function testGetLssCellData(
        int $x,
        int $y,
        string $expectedBackGroundId,
        bool $isEncoded,
        string $expectedMapGraphicPath
    ): void {
        $data = mock(VisualNavPanelEntryData::class);
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

        $subject = new VisualNavPanelEntry($data, $layer, $encodedMap);

        $result = $subject->getLssCellData();

        $this->assertEquals($expectedBackGroundId, $result->getSystemBackgroundId());
        $this->assertEquals($expectedMapGraphicPath, $result->getMapGraphicPath());
        $this->assertTrue($result->getColonyShieldState());
        $this->assertEquals("1222", $result->getSubspaceCode());
    }
}
