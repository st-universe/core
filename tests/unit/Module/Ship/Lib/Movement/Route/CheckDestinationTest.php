<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib\Movement\Route;

use Mockery\MockInterface;
use Override;
use PHPUnit\Framework\Attributes\DataProvider;
use RuntimeException;
use Stu\Exception\SanityCheckException;
use Stu\Orm\Entity\LayerInterface;
use Stu\Orm\Entity\MapInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\StarSystemInterface;
use Stu\Orm\Entity\StarSystemMapInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

class CheckDestinationTest extends StuTestCase
{
    /** @var MockInterface&MapRepositoryInterface */
    private MockInterface $mapRepository;

    /** @var MockInterface&StarSystemMapRepositoryInterface */
    private MockInterface $starSystemMapRepository;

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
        $start = $this->mock(MapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $layer = $this->mock(LayerInterface::class);
        $destination = $returnResult ? $this->mock(MapInterface::class) : null;

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
        $start = $this->mock(StarSystemMapInterface::class);
        $ship = $this->mock(ShipInterface::class);
        $system = $this->mock(StarSystemInterface::class);
        $destination = $returnResult ? $this->mock(StarSystemMapInterface::class) : null;

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
