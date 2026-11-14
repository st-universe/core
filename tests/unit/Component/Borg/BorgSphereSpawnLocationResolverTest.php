<?php

declare(strict_types=1);

namespace Stu\Component\Borg;

use PHPUnit\Framework\Attributes\DataProvider;
use Stu\Lib\Information\InformationWrapper;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\StuTestCase;

final class BorgSphereSpawnLocationResolverTest extends StuTestCase
{
    /** @return iterable<string, array{string|false, string|false, string|false}> */
    public static function emptyCoordinates(): iterable
    {
        yield 'omitted fields' => [false, false, false];
        yield 'empty fields' => ['', '', ''];
        yield 'blank fields' => [' ', '  ', "\t"];
    }

    #[DataProvider('emptyCoordinates')]
    public function testEmptyCoordinatesKeepAutomaticSpawningWithoutDatabaseLookups(string|false $layer, string|false $x, string|false $y): void
    {
        $layers = $this->mock(LayerRepositoryInterface::class);
        $layers->shouldNotReceive('find');
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldNotReceive('getByCoordinates');
        $information = new InformationWrapper();

        self::assertNull(new BorgSphereSpawnLocationResolver($layers, $maps)->resolve($layer, $x, $y, $information));
        self::assertTrue($information->isEmpty());
    }

    /** @return iterable<string, array{string|false, string|false, string|false}> */
    public static function invalidCoordinates(): iterable
    {
        yield 'missing layer' => [false, '80', '47'];
        yield 'missing X' => ['2', '', '47'];
        yield 'missing Y' => ['2', '80', false];
        yield 'zero layer is not an empty request' => ['0', '', ''];
        yield 'zero X' => ['2', '0', '47'];
        yield 'zero Y' => ['2', '80', '0'];
        yield 'negative number' => ['2', '-80', '47'];
        yield 'fraction' => ['2', '80.5', '47'];
        yield 'scientific notation' => ['2', '8e1', '47'];
        yield 'non-numeric value' => ['2', 'planet', '47'];
        yield 'signed positive value' => ['+2', '80', '47'];
        yield 'overflow' => ['2', '9999999999999999999999999999', '47'];
    }

    #[DataProvider('invalidCoordinates')]
    public function testIncompleteAndInvalidCoordinatesAreRejectedBeforeDatabaseLookups(string|false $layer, string|false $x, string|false $y): void
    {
        $layers = $this->mock(LayerRepositoryInterface::class);
        $layers->shouldNotReceive('find');
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldNotReceive('getByCoordinates');
        $information = new InformationWrapper();

        self::assertFalse(new BorgSphereSpawnLocationResolver($layers, $maps)->resolve($layer, $x, $y, $information));
        self::assertCount(1, $information->getInformations());
    }

    public function testAValidExistingFieldIsReturnedWithoutAnySafetyOrRadiusCheck(): void
    {
        $layer = new Layer();
        $location = new Map();
        $layers = $this->mock(LayerRepositoryInterface::class);
        $layers->shouldReceive('find')->once()->with(2)->andReturn($layer);
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldReceive('getByCoordinates')->once()->with($layer, 80, 47)->andReturn($location);
        $information = new InformationWrapper();

        self::assertSame($location, new BorgSphereSpawnLocationResolver($layers, $maps)->resolve(' 02 ', '0080', '47', $information));
        self::assertTrue($information->isEmpty());
    }

    public function testAnUnknownLayerReturnsAnInformationMessage(): void
    {
        $layers = $this->mock(LayerRepositoryInterface::class);
        $layers->shouldReceive('find')->once()->with(2)->andReturn(null);
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldNotReceive('getByCoordinates');
        $information = new InformationWrapper();

        self::assertFalse(new BorgSphereSpawnLocationResolver($layers, $maps)->resolve('2', '80', '47', $information));
        self::assertSame(['Der angegebene Layer existiert nicht'], $information->getInformations());
    }

    public function testAnUnknownMapFieldReturnsAnInformationMessage(): void
    {
        $layer = new Layer();
        $layers = $this->mock(LayerRepositoryInterface::class);
        $layers->shouldReceive('find')->once()->with(2)->andReturn($layer);
        $maps = $this->mock(MapRepositoryInterface::class);
        $maps->shouldReceive('getByCoordinates')->once()->with($layer, 80, 47)->andReturn(null);
        $information = new InformationWrapper();

        self::assertFalse(new BorgSphereSpawnLocationResolver($layers, $maps)->resolve('2', '80', '47', $information));
        self::assertSame(['An den angegebenen Koordinaten existiert kein Kartenfeld'], $information->getInformations());
    }
}
