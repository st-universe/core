<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\Ship;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

class CheckDestinationTest extends StuTestCase
{
    private MockInterface&MapRepositoryInterface $mapRepository;

    private MockInterface&StarSystemMapRepositoryInterface $starSystemMapRepository;

    private CheckDestinationInterface $subject;

    #[Override]
    protected function setUp(): void
    {
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->starSystemMapRepository = $this->mock(StarSystemMapRepositoryInterface::class);

        $this->subject = new CheckDestination(
            $this->mapRepository,
            $this->starSystemMapRepository
        );
    }

    public static function provideValidateForMapDestinationData(): array
    {
        return [
            //startX, startY, destX, destY, layerW, layerH, sanity?, finalX, finalY, result?
            //invalid
            [1,         2,     3,      4,   null,   null,    true,     null,   null,   null],
            //layer boundaries
            [1,         2,     1,      9,      1,      7,   false,        1,      7,   false],
            [1,         2,     9,      2,      7,      2,   false,        7,      2,   true],
            //lower Boundaries
            [0,         2,     1,      2,      1,      2,   false,        1,      2,   true],
            [2,         0,     2,      1,      2,      1,   false,        2,      1,   true],
            //all ok
            [1,         2,     1,      9,      1,      9,   false,        1,      9,   true],
            [9,         1,     9,      1,      9,      1,   false,        9,      1,   true],
        ];
    }

    #[DataProvider('provideValidateForMapDestinationData')]
    public function testValidateForMapDestination(
        int $startX,
        int $startY,
        int $destX,
        int $destY,
        ?int $layerWidth,
        ?int $layerHeight,
        bool $expectSanityException,
        ?int $finalX,
        ?int $finalY,
        ?bool $returnResult
    ): void {
        $start = $this->mock(Map::class);
        $ship = $this->mock(Ship::class);
        $layer = $this->mock(Layer::class);
        $destination = $returnResult ? $this->mock(Map::class) : null;

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $start->shouldReceive('getX')
            ->withNoArgs()
            ->andReturn($startX);
        $start->shouldReceive('getY')
            ->withNoArgs()
            ->andReturn($startY);

        if ($expectSanityException) {
            static::expectExceptionMessage(sprintf(
                'userId 42 tried to navigate from %d|%d to invalid position %d|%d',
                $startX,
                $startY,
                $destX,
                $destY
            ));
            static::expectException(SanityCheckException::class);

            $ship->shouldReceive('getUser->getId')
                ->withNoArgs()
                ->andReturn(42);
            $start->shouldReceive('getSectorString')
                ->withNoArgs()
                ->andReturn(sprintf("%d|%d", $startX, $startY));
        } else {
            $start->shouldReceive('getLayer')
                ->withNoArgs()
                ->once()
                ->andReturn($layer);

            $layer->shouldReceive('getWidth')
                ->withNoArgs()
                ->andReturn($layerWidth);
            $layer->shouldReceive('getHeight')
                ->withNoArgs()
                ->andReturn($layerHeight);

            $this->mapRepository->shouldReceive('getByCoordinates')
                ->with($layer, $finalX, $finalY)
                ->once()
                ->andReturn($destination);

            if (!$returnResult) {
                static::expectExceptionMessage(sprintf(
                    'destination %d|%d does not exist',
                    $finalX,
                    $finalY
                ));
                static::expectException(RuntimeException::class);
            }
        }

        $result = $this->subject->validate($ship, $destX, $destY);

        if ($returnResult) {
            $this->assertEquals($destination, $result);
        }
    }

    public static function provideValidateForSystemMapDestinationData(): array
    {
        return [
            //startX, startY, destX, destY, systemW, systemH, sanity?, finalX, finalY, result?
            //invalid
            [1,         2,     3,      4,   null,   null,    true,     null,   null,   null],
            //layer boundaries
            [1,         2,     1,      9,      1,      7,   false,        1,      7,   false],
            [1,         2,     9,      2,      7,      2,   false,        7,      2,   true],
            //lower Boundaries
            [0,         2,     1,      2,      1,      2,   false,        1,      2,   true],
            [2,         0,     2,      1,      2,      1,   false,        2,      1,   true],
            //all ok
            [1,         2,     1,      9,      1,      9,   false,        1,      9,   true],
            [9,         1,     9,      1,      9,      1,   false,        9,      1,   true],
        ];
    }

    #[DataProvider('provideValidateForSystemMapDestinationData')]
    public function testValidateForSystemMapDestination(
        int $startX,
        int $startY,
        int $destX,
        int $destY,
        ?int $systemWidth,
        ?int $systemHeight,
        bool $expectSanityException,
        ?int $finalX,
        ?int $finalY,
        ?bool $returnResult
    ): void {
        $start = $this->mock(StarSystemMap::class);
        $ship = $this->mock(Ship::class);
        $system = $this->mock(StarSystem::class);
        $destination = $returnResult ? $this->mock(StarSystemMap::class) : null;

        $ship->shouldReceive('getLocation')
            ->withNoArgs()
            ->andReturn($start);

        $start->shouldReceive('getX')
            ->withNoArgs()
            ->andReturn($startX);
        $start->shouldReceive('getY')
            ->withNoArgs()
            ->andReturn($startY);

        if ($expectSanityException) {
            static::expectExceptionMessage(sprintf(
                'userId 42 tried to navigate from %d|%d to invalid position %d|%d',
                $startX,
                $startY,
                $destX,
                $destY
            ));
            static::expectException(SanityCheckException::class);

            $ship->shouldReceive('getUser->getId')
                ->withNoArgs()
                ->andReturn(42);
            $start->shouldReceive('getSectorString')
                ->withNoArgs()
                ->andReturn(sprintf("%d|%d", $startX, $startY));
        } else {
            $start->shouldReceive('getSystem')
                ->withNoArgs()
                ->once()
                ->andReturn($system);

            $system->shouldReceive('getId')
                ->withNoArgs()
                ->once()
                ->andReturn(5);
            $system->shouldReceive('getMaxX')
                ->withNoArgs()
                ->andReturn($systemWidth);
            $system->shouldReceive('getMaxY')
                ->withNoArgs()
                ->andReturn($systemHeight);

            $this->starSystemMapRepository->shouldReceive('getByCoordinates')
                ->with(5, $finalX, $finalY)
                ->once()
                ->andReturn($destination);

            if (!$returnResult) {
                static::expectExceptionMessage(sprintf(
                    'destination %d|%d does not exist',
                    $finalX,
                    $finalY
                ));
                static::expectException(RuntimeException::class);
            }
        }

        $result = $this->subject->validate($ship, $destX, $destY);

        if ($returnResult) {
            $this->assertEquals($destination, $result);
        }
    }
}
