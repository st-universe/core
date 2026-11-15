<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\PreserveGlobalState;
use PHPUnit\Framework\Attributes\RunInSeparateProcess;
use ReflectionProperty;
use RuntimeException;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Cache\PrivateCacheDirectory;
use Stu\Module\Admin\View\Map\LiveMap\ShowLiveMapImage;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Starmap\View\ShowUserStarmapImage\ShowUserStarmapImage;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Entity\MapFieldType;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;
use Stu\StuTestCase;
use Throwable;

final class MapImageCacheSecurityTest extends StuTestCase
{
    private string $sandbox;
    private string $directory;
    private PrivateCacheDirectory $cache;
    private Layer $layer;

    protected function setUp(): void
    {
        $this->sandbox = sys_get_temp_dir() . '/stu-map-cache-test-' . bin2hex(random_bytes(8));
        mkdir($this->sandbox, 0o700);
        $this->directory = $this->sandbox . '/cache';
        $this->cache = new PrivateCacheDirectory($this->directory);
        $this->layer = new Layer();
        foreach (['id' => 11, 'width' => 2, 'height' => 1] as $property => $value) {
            new ReflectionProperty(Layer::class, $property)->setValue($this->layer, $value);
        }
        file_put_contents($this->sandbox . '/sentinel', 'testdata-must-remain');
    }

    protected function tearDown(): void
    {
        foreach (glob($this->directory . '/*') ?: [] as $path) {
            unlink($path);
        }
        rmdir($this->directory);
        foreach (glob($this->sandbox . '/assets/map/11/*') ?: [] as $path) {
            unlink($path);
        }
        if (is_dir($this->sandbox . '/assets')) {
            rmdir($this->sandbox . '/assets/map/11');
            rmdir($this->sandbox . '/assets/map');
            rmdir($this->sandbox . '/assets');
        }
        if (is_dir($this->sandbox . '/app')) {
            rmdir($this->sandbox . '/app/src');
            rmdir($this->sandbox . '/app');
        }
        foreach (['sentinel', 'renders'] as $name) {
            if (is_file($this->sandbox . '/' . $name)) {
                unlink($this->sandbox . '/' . $name);
            }
        }
        rmdir($this->sandbox);
        parent::tearDown();
    }

    #[DataProvider('linkCases')]
    public function testBothControllersRejectCacheAndLockSymlinks(bool $admin, bool $lock): void
    {
        $controller = $this->controller($admin);
        $path = $this->getMethod($controller, $admin ? 'getCachePath' : 'getBaseCachePath')->invoke($controller, $this->layer);
        $link = $path . ($lock ? '.lock' : '');
        symlink($this->sandbox . '/sentinel', $link);
        try {
            if ($admin) {
                $this->getMethod($controller, 'ensureCacheFile')->invoke($controller, $this->layer, $path, false);
            } else {
                $this->getMethod($controller, 'ensureBaseCacheFile')->invoke($controller, $this->layer, $path);
            }
            self::fail('Verknüpfung wurde akzeptiert');
        } catch (RuntimeException) {
            self::assertSame('testdata-must-remain', file_get_contents($this->sandbox . '/sentinel'));
            self::assertTrue(is_link($link));
        }
    }

    public static function linkCases(): iterable
    {
        yield 'player cache' => [false, false];
        yield 'player lock' => [false, true];
        yield 'admin cache' => [true, false];
        yield 'admin lock' => [true, true];
    }

    public function testReplacedTemporarySymlinkIsRejectedWithoutFollowingItsTarget(): void
    {
        $temporary = $this->cache->createTemporary('map.png');
        self::assertSame(0o600, $temporary->fstat()['mode'] & 0o777);
        self::assertSame(0o700, fileperms($this->directory) & 0o777);
        unlink($temporary->getPathname());
        symlink($this->sandbox . '/sentinel', $temporary->getPathname());
        $temporary->fwrite('safe-open-descriptor');
        try {
            $this->cache->replace($temporary, 'map.png');
            self::fail('Temporäre Verknüpfung wurde akzeptiert');
        } catch (RuntimeException) {
            self::assertSame('testdata-must-remain', file_get_contents($this->sandbox . '/sentinel'));
            self::assertFileDoesNotExist($this->directory . '/map.png');
        }
    }

    public function testAtomicReplacementRejectsAPrecreatedTargetSymlink(): void
    {
        $temporary = $this->cache->createTemporary('map.png');
        $temporary->fwrite('png-test-content');
        symlink($this->sandbox . '/sentinel', $this->directory . '/map.png');
        $this->expectException(RuntimeException::class);
        try {
            $this->cache->replace($temporary, 'map.png');
        } finally {
            self::assertSame('testdata-must-remain', file_get_contents($this->sandbox . '/sentinel'));
        }
    }

    public function testMaskingPreservesExploredPixelsAndHidesOtherFields(): void
    {
        $this->prepareTiles();
        $controller = $this->controller(false, true);
        $base = $this->getMethod($controller, 'getBaseCachePath')->invoke($controller, $this->layer);
        $this->getMethod($controller, 'ensureBaseCacheFile')->invoke($controller, $this->layer, $base);
        $user = new User();
        new ReflectionProperty(User::class, 'id')->setValue($user, 101);
        $masked = $this->getMethod($controller, 'getMaskedCachePath')->invoke($controller, $this->layer, $user, 'test-version', time());
        $this->getMethod($controller, 'ensureMaskedCacheFile')->invoke($controller, $this->layer, $base, [['y' => 1, 'startX' => 1, 'endX' => 1]], $masked);
        $image = imagecreatefrompng($masked);
        self::assertNotFalse($image);
        self::assertSame(0xff0000, imagecolorat($image, 15, 15));
        self::assertSame(0x000000, imagecolorat($image, 45, 15));
        self::assertSame(0o600, fileperms($masked) & 0o777);
        self::assertCount(0, glob($this->directory . '/*.tmp') ?: []);
    }

    /** @return iterable<string, array{list<array{y: int, startX: int, endX: int}>, list<array{int, int}>}> */
    public static function visibilityGaps(): iterable
    {
        yield 'no explored fields' => [[], []];
        yield 'whole layer explored' => [[
            ['y' => 1, 'startX' => 1, 'endX' => 6],
            ['y' => 2, 'startX' => 1, 'endX' => 6],
            ['y' => 3, 'startX' => 1, 'endX' => 6],
            ['y' => 4, 'startX' => 1, 'endX' => 6]
        ], array_merge(...array_map(static fn (int $y): array => array_map(static fn (int $x): array => [$x, $y], range(1, 6)), range(1, 4)))];
        yield 'row gaps and separated runs' => [[
            ['y' => 2, 'startX' => 2, 'endX' => 3],
            ['y' => 2, 'startX' => 5, 'endX' => 5],
            ['y' => 4, 'startX' => 1, 'endX' => 1]
        ], [[2, 2], [3, 2], [5, 2], [1, 4]]];
        yield 'outer corners' => [[
            ['y' => 1, 'startX' => 1, 'endX' => 1],
            ['y' => 4, 'startX' => 6, 'endX' => 6]
        ], [[1, 1], [6, 4]]];
    }

    /**
     * @param list<array{y: int, startX: int, endX: int}> $runs
     * @param list<array{int, int}> $visible
     */
    #[DataProvider('visibilityGaps')]
    public function testMaskingHidesGapsAcrossRowsWithoutChangingVisiblePixels(array $runs, array $visible): void
    {
        new ReflectionProperty(Layer::class, 'width')->setValue($this->layer, 6);
        new ReflectionProperty(Layer::class, 'height')->setValue($this->layer, 4);
        $controller = $this->controller(false);
        $baseFile = $this->cache->createTemporary('base.png');
        $base = imagecreatetruecolor(180, 120);
        imagefill($base, 0, 0, 0x123456);
        imagepng($base, $baseFile->getPathname());
        $target = $this->cache->createTemporary('masked.png');

        $this->getMethod($controller, 'renderMaskedLayerImage')->invoke($controller, $this->layer, $baseFile->getPathname(), $runs, $target);

        $image = imagecreatefrompng($target->getPathname());
        self::assertNotFalse($image);
        foreach (range(1, 4) as $y) {
            foreach (range(1, 6) as $x) {
                self::assertSame(in_array([$x, $y], $visible, true) ? 0x123456 : 0x000000, imagecolorat($image, ($x - 1) * 30 + 15, ($y - 1) * 30 + 15), sprintf('Feld %d|%d', $x, $y));
            }
        }
    }

    #[RunInSeparateProcess]
    #[PreserveGlobalState(false)]
    public function testFullSizeLayerAndItsMaskRenderBelowTheServerMemoryLimit(): void
    {
        $limit = ini_get('memory_limit');
        ini_set('memory_limit', '128M');
        try {
            new ReflectionProperty(Layer::class, 'width')->setValue($this->layer, 120);
            new ReflectionProperty(Layer::class, 'height')->setValue($this->layer, 120);
            $this->prepareTiles();
            $controller = $this->controller(false);
            $maps = new ReflectionProperty(ShowUserStarmapImage::class, 'mapRepository')->getValue($controller);
            $maps->shouldNotReceive('getAllOrdered');
            $maps->shouldReceive('iterateGraphicFields')->once()->with($this->layer)->andReturnUsing(static function (): iterable {
                foreach (range(1, 120) as $y) {
                    foreach (range(1, 120) as $x) {
                        yield ['x' => $x, 'y' => $y, 'type' => 1];
                    }
                }
            });
            $base = $this->getMethod($controller, 'getBaseCachePath')->invoke($controller, $this->layer);
            $target = $this->cache->createTemporary('full-layer-mask.png');
            $runs = array_map(static fn (int $y): array => ['y' => $y, 'startX' => 1, 'endX' => 60], range(2, 120, 2));
            $before = memory_get_usage(true);
            memory_reset_peak_usage();

            $this->getMethod($controller, 'ensureBaseCacheFile')->invoke($controller, $this->layer, $base);
            $this->getMethod($controller, 'renderMaskedLayerImage')->invoke($controller, $this->layer, $base, $runs, $target);

            self::assertLessThan(24 * 1024 * 1024, memory_get_peak_usage(true) - $before);
            self::assertSame([3600, 3600], array_slice(getimagesize($target->getPathname()), 0, 2));
            $image = imagecreatefrompng($target->getPathname());
            self::assertNotFalse($image);
            self::assertSame(0xff0000, imagecolorat($image, 15, 45));
            self::assertSame(0x000000, imagecolorat($image, 1815, 45));
            self::assertSame(0x000000, imagecolorat($image, 15, 15));
        } finally {
            ini_set('memory_limit', $limit);
        }
    }

    #[DataProvider('controllers')]
    public function testConcurrentRenderingPublishesOneCompletePrivatePng(bool $admin): void
    {
        $controller = $this->controller($admin, false, true);
        $path = $this->getMethod($controller, $admin ? 'getCachePath' : 'getBaseCachePath')->invoke($controller, $this->layer);
        $children = [];
        for ($index = 0; $index < 3; $index++) {
            $pid = pcntl_fork();
            self::assertNotSame(-1, $pid);
            if ($pid === 0) {
                try {
                    if ($admin) {
                        $this->getMethod($controller, 'ensureCacheFile')->invoke($controller, $this->layer, $path, false);
                    } else {
                        $this->getMethod($controller, 'ensureBaseCacheFile')->invoke($controller, $this->layer, $path);
                    }
                    exit(0);
                } catch (Throwable) {
                    exit(1);
                }
            }
            $children[] = $pid;
        }
        foreach ($children as $pid) {
            pcntl_waitpid($pid, $status);
            self::assertSame(0, pcntl_wexitstatus($status));
        }
        self::assertSame("render\n", file_get_contents($this->sandbox . '/renders'));
        self::assertSame([60, 30], array_slice(getimagesize($path), 0, 2));
        self::assertSame(0o600, fileperms($path) & 0o777);
        self::assertCount(0, glob($this->directory . '/*.tmp') ?: []);
    }

    public static function controllers(): iterable
    {
        yield 'player' => [false];
        yield 'admin' => [true];
    }

    private function prepareTiles(): void
    {
        mkdir($this->sandbox . '/assets/map/11', 0o700, true);
        mkdir($this->sandbox . '/app/src', 0o700, true);
        foreach ([1 => 0xff0000, 2 => 0x0000ff] as $type => $color) {
            $tile = imagecreatetruecolor(30, 30);
            self::assertNotFalse($tile);
            imagefill($tile, 0, 0, $color);
            imagepng($tile, $this->sandbox . '/assets/map/11/' . $type . '.png');
        }
    }

    private function controller(bool $admin, bool $tiles = false, bool $countRenders = false): ShowLiveMapImage|ShowUserStarmapImage
    {
        $maps = $this->mock(MapRepositoryInterface::class);
        $fields = [];
        if ($tiles) {
            foreach ([1, 2] as $x) {
                $map = $this->mock(Map::class);
                $map->shouldReceive('getX')->andReturn($x);
                $map->shouldReceive('getY')->andReturn(1);
                $map->shouldReceive('getFieldType')->andReturn(new MapFieldType()->setType($x));
                $fields[] = $map;
            }
        }
        $maps->shouldReceive($admin ? 'getAllOrdered' : 'iterateGraphicFields')->andReturnUsing(function () use ($fields, $countRenders, $admin) {
            if ($countRenders) {
                file_put_contents($this->sandbox . '/renders', "render\n", FILE_APPEND | LOCK_EX);
            }
            return $admin ? $fields : array_map(static fn (Map $map): array => ['x' => $map->getX(), 'y' => $map->getY(), 'type' => $map->getFieldType()->getType()], $fields);
        })->byDefault();
        $config = $this->mock(StuConfigInterface::class);
        $config->shouldReceive('getGameSettings->getWebroot')->andReturn($this->sandbox . '/app/src/Public');
        $layers = $this->mock(LayerRepositoryInterface::class);
        $encoded = $this->mock(EncodedMapInterface::class);
        return $admin
            ? new ShowLiveMapImage($maps, $layers, $config, $encoded, $this->cache)
            : new ShowUserStarmapImage($maps, $layers, $this->mock(UserMapRepositoryInterface::class), $config, $this->mock(StuHashInterface::class), $encoded, $this->cache);
    }
}
