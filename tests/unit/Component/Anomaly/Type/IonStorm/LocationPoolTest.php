<?php

declare(strict_types=1);

namespace Stu\Component\Anomaly\Type\IonStorm;

use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\Location;
use Stu\StuTestCase;

class LocationPoolTest extends StuTestCase
{
    private LocationPool $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->subject = new LocationPool(
            [
                '1_2' => $this->mock(Location::class),
                '2_1' => $this->mock(Location::class),
                '2_2' => $this->mock(Location::class),
                '2_3' => $this->mock(Location::class),
                '3_2' => $this->mock(Location::class)
            ]
        );
    }

    public static function getNeighboursDataProvider(): array
    {
        return [
            [0, 2, 1],
            [2, 0, 1],
            [4, 2, 1],
            [2, 4, 1],
            [1, 1, 2],
            [3, 1, 2],
            [3, 3, 2],
            [1, 3, 2],
            [2, 2, 4],
            [5, 5, 0]
        ];
    }
    #[DataProvider('getNeighboursDataProvider')]
    public function testGetNeighbours(int $x, int $y, int $expectedNeighbourAmount): void
    {
        $location = $this->mock(Location::class);

        $location->shouldReceive('getX')
            ->withNoArgs()
            ->andReturn($x);
        $location->shouldReceive('getY')
            ->withNoArgs()
            ->andReturn($y);

        $result = $this->subject->getNeighbours($location);

        $this->assertEquals($expectedNeighbourAmount, count($result));
    }
}
