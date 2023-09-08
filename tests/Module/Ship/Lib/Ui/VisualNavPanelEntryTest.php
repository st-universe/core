<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Ui;

use Stu\StuTestCase;

class VisualNavPanelEntryTest extends StuTestCase
{
    public static function provideGetSystemBackgroundIdData()
    {
        return [
            [1, 2, '0201'],
            [23, 2, '0223'],
            [7, 14, '1407'],
            [32, 16, '1632'],
        ];
    }

    /**
     * @dataProvider provideGetSystemBackgroundIdData
     */
    public function testGetSystemBackgroundId(int $x, int $y, string $expected): void
    {
        $array = ['posx' => $x, 'posy' => $y];

        $subject = new VisualNavPanelEntry($array);

        $result = $subject->getSystemBackgroundId();

        $this->assertEquals($expected, $result);
    }
}
