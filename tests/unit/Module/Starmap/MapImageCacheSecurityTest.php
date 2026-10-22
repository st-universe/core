<?php

declare(strict_types=1);

namespace Stu\Module\Starmap;

use PHPUnit\Framework\Attributes\DataProvider;
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
        $maps->shouldReceive('getAllOrdered')->andReturnUsing(function () use ($fields, $countRenders) {
            if ($countRenders) {
                file_put_contents($this->sandbox . '/renders', "render\n", FILE_APPEND | LOCK_EX);
            }
            return $fields;
        });
        $config = $this->mock(StuConfigInterface::class);
        $config->shouldReceive('getGameSettings->getWebroot')->andReturn($this->sandbox . '/app/src/Public');
        $layers = $this->mock(LayerRepositoryInterface::class);
        $encoded = $this->mock(EncodedMapInterface::class);
        return $admin
            ? new ShowLiveMapImage($maps, $layers, $config, $encoded, $this->cache)
            : new ShowUserStarmapImage($maps, $layers, $this->mock(UserMapRepositoryInterface::class), $config, $this->mock(StuHashInterface::class), $encoded, $this->cache);
    }
}
