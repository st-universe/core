<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib\Movement\Route;

use InvalidArgumentException;
use Mockery\MockInterface;
use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\StarSystem;
use Stu\Orm\Entity\StarSystemMap;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;
use Stu\StuTestCase;

class LoadWaypointsTest extends StuTestCase
{
    private MockInterface&MapRepositoryInterface $mapRepository;

    private MockInterface&StarSystemMapRepositoryInterface $starSystemMapRepository;

    private LoadWaypointsInterface $subject;

    #[\Override]
    protected function setUp(): void
    {
        $this->mapRepository = $this->mock(MapRepositoryInterface::class);
        $this->starSystemMapRepository = $this->mock(StarSystemMapRepositoryInterface::class);

        $this->subject = new LoadWaypoints(
            $this->mapRepository,
            $this->starSystemMapRepository
        );
    }

    public function testLoadExpectExceptionWhenCalledWithDifferentTypes1(): void
    {
        static::expectExceptionMessage('start and destination have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->load(
            $this->mock(Map::class),
            $this->mock(StarSystemMap::class)
        );
    }

    public function testLoadExpectExceptionWhenCalledWithDifferentTypes2(): void
    {
        static::expectExceptionMessage('start and destination have different type');
        static::expectException(InvalidArgumentException::class);

        $this->subject->load(
            $this->mock(StarSystemMap::class),
            $this->mock(Map::class)
        );
    }

    public static function provideTestLoadForMapFieldsData(): array
    {
        return [
            //startX, destX, startY,  destY, sortAscending?
            [1,         1,      1,      2,      true],
            [1,         2,      1,      1,      true],
            [2,         1,      1,      1,      false],
            [1,         1,      2,      1,      false],
        ];
    }

    #[DataProvider('provideTestLoadForMapFieldsData')]
    public function testLoadForMapFields(
        int $startX,
        int $destX,
        int $startY,
        int $destY,
        bool $sortAscending
    ): void {
        $start = $this->mock(Map::class);
        $destination = $this->mock(Map::class);
        $waypoints = [$start, $destination];

        $start->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($startX);
        $start->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($startY);
        $start->shouldReceive('getLayer->getId')
            ->withNoArgs()
            ->once()
            ->andReturn(42);

        $destination->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($destX);
        $destination->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($destY);

        $this->mapRepository->shouldReceive('getByCoordinateRange')
            ->with(
                42,
                min($startX, $destX),
                max($startX, $destX),
                min($startY, $destY),
                max($startY, $destY),
                $sortAscending
            )
            ->once()
            ->andReturn($waypoints);

        $result = $this->subject->load($start, $destination);

        $this->assertEquals(1, $result->count());
    }

    public static function provideTestLoadForSystemMapFieldsData(): array
    {
        return [
            //startX, destX, startY,  destY, sortAscending?
            [1,         1,      1,      2,      true],
            [1,         2,      1,      1,      true],
            [2,         1,      1,      1,      false],
            [1,         1,      2,      1,      false],
        ];
    }

    #[DataProvider('provideTestLoadForSystemMapFieldsData')]
    public function testLoadForSystemMapFields(
        int $startX,
        int $destX,
        int $startY,
        int $destY,
        bool $sortAscending
    ): void {
        $start = $this->mock(StarSystemMap::class);
        $destination = $this->mock(StarSystemMap::class);
        $system = $this->mock(StarSystem::class);
        $waypoints = [$start, $destination];

        $start->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($startX);
        $start->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($startY);
        $start->shouldReceive('getSystem')
            ->withNoArgs()
            ->once()
            ->andReturn($system);

        $system->shouldReceive('getId')
            ->withNoArgs()
            ->once()
            ->andReturn(424242);

        $destination->shouldReceive('getX')
            ->withNoArgs()
            ->once()
            ->andReturn($destX);
        $destination->shouldReceive('getY')
            ->withNoArgs()
            ->once()
            ->andReturn($destY);

        $this->starSystemMapRepository->shouldReceive('getByCoordinateRange')
            ->with(
                424242,
                min($startX, $destX),
                max($startX, $destX),
                min($startY, $destY),
                max($startY, $destY),
                $sortAscending
            )
            ->once()
            ->andReturn($waypoints);

        $result = $this->subject->load($start, $destination);

        $this->assertEquals(1, $result->count());
    }
}
