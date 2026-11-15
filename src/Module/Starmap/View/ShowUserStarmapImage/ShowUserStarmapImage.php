<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapImage;

use GdImage;
use InvalidArgumentException;
use Override;
use request;
use RuntimeException;
use SplFileObject;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Cache\PrivateCacheDirectory;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\Component\View\ViewControllerContext;
use Stu\Module\Control\StuHashInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\User;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\UserMapRepositoryInterface;

final class ShowUserStarmapImage implements ViewControllerInterface
{
    use LayerExplorationTrait;

    public const string VIEW_IDENTIFIER = 'SHOW_USER_STARMAP_IMAGE';
    public const int CELL_SIZE = 30;

    private const int BASE_CACHE_TTL = 1800;

    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private UserMapRepositoryInterface $userMapRepository,
        private StuConfigInterface $config,
        private StuHashInterface $stuHash,
        private EncodedMapInterface $encodedMap,
        private PrivateCacheDirectory $cacheDirectory
    ) {}

    #[Override]
    public function handle(ViewControllerContext $game): void
    {
        $layerId = request::getIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if (!$layer instanceof Layer) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $user = $game->getUser();
        if (!$this->hasSeen($user, $layer)) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $baseCachePath = $this->getBaseCachePath($layer);
        $this->ensureBaseCacheFile($layer, $baseCachePath);
        $baseFile = $this->cacheDirectory->open(basename($baseCachePath));
        $baseMtime = $baseFile->fstat()['mtime'];

        $visibility = $this->getVisibility($user, $layer);
        $lock = $this->cacheDirectory->open(sprintf('stu-user-starmap-mask-u%d-l%d.lock', $user->getId(), $layer->getId()));
        if (!$lock->flock(LOCK_EX)) {
            throw new RuntimeException('Sichtbarer Kartencache kann nicht gesperrt werden');
        }
        try {
            if ($visibility['full']) {
                $this->cleanupOldMaskedCaches($layer, $user, null);
                $this->sendImage($baseCachePath, $this->buildEtag($layer, $user, $visibility['version'], $baseMtime));
            }
            $maskedCachePath = $this->getMaskedCachePath($layer, $user, $visibility['version'], $baseMtime);
            $this->ensureMaskedCacheFile($layer, $baseCachePath, $visibility['runs'], $maskedCachePath);
            $this->cleanupOldMaskedCaches($layer, $user, $maskedCachePath);
            $this->sendImage($maskedCachePath, $this->buildEtag($layer, $user, $visibility['version'], $baseMtime));
        } finally {
            $lock->flock(LOCK_UN);
        }
    }

    /**
     * @return array{full: bool, version: string, runs: array<int, array{y: int, startX: int, endX: int}>}
     */
    private function getVisibility(User $user, Layer $layer): array
    {
        if ($this->hasExplored($user, $layer)) {
            return [
                'full' => true,
                'version' => 'full',
                'runs' => []
            ];
        }

        $runs = $this->userMapRepository->getVisibleMapFieldRuns($user->getId(), $layer->getId());

        return [
            'full' => false,
            'version' => $this->buildRunsVersion($runs),
            'runs' => $runs
        ];
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function buildRunsVersion(array $runs): string
    {
        $hash = hash_init($this->config->getGameSettings()->getHashMethod());

        foreach ($runs as $run) {
            hash_update($hash, sprintf('%d:%d-%d;', $run['y'], $run['startX'], $run['endX']));
        }

        return hash_final($hash);
    }

    private function ensureBaseCacheFile(Layer $layer, string $cachePath): void
    {
        $lock = $this->cacheDirectory->open(basename($cachePath) . '.lock');
        if (!$lock->flock(LOCK_EX)) {
            throw new RuntimeException('Kartencache kann nicht gesperrt werden');
        }
        $temporary = null;
        try {
            $cachePath = $this->cacheDirectory->path(basename($cachePath));
            if (is_file($cachePath) && filemtime($cachePath) > time() - self::BASE_CACHE_TTL) {
                return;
            }
            $temporary = $this->cacheDirectory->createTemporary(basename($cachePath));
            $this->renderLayerImage($layer, $temporary);
            $this->cacheDirectory->replace($temporary, basename($cachePath));
        } finally {
            if ($temporary !== null && is_file($temporary->getPathname())) {
                unlink($this->cacheDirectory->path($temporary->getFilename()));
            }
            $lock->flock(LOCK_UN);
        }
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function ensureMaskedCacheFile(Layer $layer, string $baseCachePath, array $runs, string $cachePath): void
    {
        $cachePath = $this->cacheDirectory->path(basename($cachePath));
        if (is_file($cachePath)) {
            return;
        }
        $temporary = $this->cacheDirectory->createTemporary(basename($cachePath));
        try {
            $this->renderMaskedLayerImage($layer, $baseCachePath, $runs, $temporary);
            $this->cacheDirectory->replace($temporary, basename($cachePath));
        } finally {
            if (is_file($temporary->getPathname())) {
                unlink($this->cacheDirectory->path($temporary->getFilename()));
            }
        }
    }

    private function renderLayerImage(Layer $layer, SplFileObject $targetFile): void
    {
        $width = $layer->getWidth() * self::CELL_SIZE;
        $height = $layer->getHeight() * self::CELL_SIZE;

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $image = imagecreatetruecolor($width, $height);
        if ($image === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        imagealphablending($image, true);
        $background = imagecolorallocate($image, 0, 0, 0);
        if ($background === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung der Hintergrundfarbe');
        }
        imagefill($image, 0, 0, $background);

        /** @var array<string, GdImage> $tileCache */
        $tileCache = [];
        foreach ($this->mapRepository->iterateGraphicFields($layer) as $map) {
            $this->renderMapField($image, $layer, $map, $tileCache);
        }

        $this->saveImage($image, $targetFile);
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function renderMaskedLayerImage(Layer $layer, string $baseCachePath, array $runs, SplFileObject $targetFile): void
    {
        $width = $layer->getWidth() * self::CELL_SIZE;
        $height = $layer->getHeight() * self::CELL_SIZE;

        if (min($width, $height) < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $baseFile = $this->cacheDirectory->open(basename($baseCachePath));
        if ($baseFile->fstat()['size'] < 1) {
            throw new RuntimeException('Kartengrafik ist leer');
        }
        $image = $this->createImageFromPng($baseFile->getPathname());
        $this->hideUnseenFields($image, $layer, $runs);
        $this->saveImage($image, $targetFile);
    }

    /** @param array<int, array{y: int, startX: int, endX: int}> $runs */
    private function hideUnseenFields(GdImage $image, Layer $layer, array $runs): void
    {
        $x = 1;
        $y = 1;
        foreach ($runs as $run) {
            if ($run['y'] !== $y) {
                $this->hideRectangle($image, $x, $y, $layer->getWidth(), $y);
                $this->hideRectangle($image, 1, $y + 1, $layer->getWidth(), $run['y'] - 1);
                $x = 1;
                $y = $run['y'];
            }
            $this->hideRectangle($image, $x, $y, $run['startX'] - 1, $y);
            $x = $run['endX'] + 1;
        }
        $this->hideRectangle($image, $x, $y, $layer->getWidth(), $y);
        $this->hideRectangle($image, 1, $y + 1, $layer->getWidth(), $layer->getHeight());
    }

    private function hideRectangle(GdImage $image, int $startX, int $startY, int $endX, int $endY): void
    {
        if ($startX <= $endX && $startY <= $endY) {
            imagefilledrectangle($image, ($startX - 1) * self::CELL_SIZE, ($startY - 1) * self::CELL_SIZE, $endX * self::CELL_SIZE - 1, $endY * self::CELL_SIZE - 1, 0);
        }
    }

    /**
     * @param array{x: int, y: int, type: int} $map
     * @param array<string, GdImage> $tileCache
     */
    private function renderMapField(GdImage $targetImage, Layer $layer, array $map, array &$tileCache): void
    {
        $imagePath = $this->getMapGraphicPath($layer, $map['type']);
        if (!is_file($imagePath)) {
            $imagePath = $this->getFallbackImagePath();
        }

        $cacheTile = !$layer->isEncoded();
        $tile = $cacheTile
            ? ($tileCache[$imagePath] ??= $this->createImageFromPng($imagePath))
            : $this->createImageFromPng($imagePath);

        $targetX = ($map['x'] - 1) * self::CELL_SIZE;
        $targetY = ($map['y'] - 1) * self::CELL_SIZE;
        $sourceWidth = imagesx($tile);
        $sourceHeight = imagesy($tile);

        if ($sourceWidth === self::CELL_SIZE && $sourceHeight === self::CELL_SIZE) {
            imagecopy($targetImage, $tile, $targetX, $targetY, 0, 0, self::CELL_SIZE, self::CELL_SIZE);
            return;
        }

        imagecopyresampled(
            $targetImage,
            $tile,
            $targetX,
            $targetY,
            0,
            0,
            self::CELL_SIZE,
            self::CELL_SIZE,
            $sourceWidth,
            $sourceHeight
        );
    }

    private function createImageFromPng(string $path): GdImage
    {
        $image = imagecreatefrompng($path);
        if ($image === false) {
            throw new InvalidArgumentException(sprintf('Fehler beim Laden der Kartengrafik: %s', $path));
        }

        return $image;
    }

    private function saveImage(GdImage $image, SplFileObject $targetFile): void
    {
        $stream = fopen('php://temp', 'w+b');
        if ($stream === false) {
            throw new RuntimeException('Kartengrafik kann nicht zwischengespeichert werden');
        }
        try {
            if (!imagepng($image, $stream, 6) || !rewind($stream)) {
                throw new RuntimeException('Kartengrafik kann nicht geschrieben werden');
            }
            while (!feof($stream)) {
                $chunk = fread($stream, 65536);
                if ($chunk === false || ($chunk !== '' && $targetFile->fwrite($chunk) !== strlen($chunk))) {
                    throw new RuntimeException('Kartengrafik kann nicht vollständig geschrieben werden');
                }
            }
        } finally {
            fclose($stream);
        }
    }

    private function sendImage(string $cachePath, string $etag): void
    {
        header('ETag: ' . $etag);
        header('Cache-Control: private, no-cache');

        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        $file = $this->cacheDirectory->open(basename($cachePath));
        header('Content-Type: image/png');
        header('Content-Length: ' . $file->fstat()['size']);
        $file->fpassthru();
        exit;
    }

    private function buildEtag(Layer $layer, User $user, string $visibilityVersion, int $baseMtime): string
    {
        return sprintf(
            '"%s"',
            $this->stuHash->hash(sprintf('%d:%d:%s:%d', $user->getId(), $layer->getId(), $visibilityVersion, $baseMtime))
        );
    }

    private function getBaseCachePath(Layer $layer): string
    {
        return $this->cacheDirectory->path(sprintf(
            'stu-user-starmap-layer-base-v1-%d-%dx%d.png',
            $layer->getId(),
            $layer->getWidth(),
            $layer->getHeight()
        ));
    }

    private function getMaskedCachePath(Layer $layer, User $user, string $visibilityVersion, int $baseMtime): string
    {
        return $this->cacheDirectory->path(sprintf(
            'stu-user-starmap-mask-v1-u%d-l%d-%dx%d-%s-%d.png',
            $user->getId(),
            $layer->getId(),
            $layer->getWidth(),
            $layer->getHeight(),
            $visibilityVersion,
            $baseMtime
        ));
    }

    private function cleanupOldMaskedCaches(Layer $layer, User $user, ?string $keepPath): void
    {
        $pattern = dirname($this->cacheDirectory->path('probe')) . '/' . sprintf(
            'stu-user-starmap-mask-v1-u%d-l%d-*.png',
            $user->getId(),
            $layer->getId()
        );
        $files = glob($pattern);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === $keepPath) {
                continue;
            }

            $file = $this->cacheDirectory->path(basename($file));
            if (is_file($file)) {
                unlink($file);
            }
        }
    }

    private function getMapGraphicPath(Layer $layer, int $fieldType): string
    {
        $basePath = $this->getMapAssetBasePath();

        if ($layer->isEncoded()) {
            return $basePath . $this->encodedMap->getEncodedMapPath($fieldType, $layer);
        }

        return sprintf('%s%d/%d.png', $basePath, $layer->getId(), $fieldType);
    }

    private function getFallbackImagePath(): string
    {
        return $this->getMapAssetBasePath() . '0.png';
    }

    private function getMapAssetBasePath(): string
    {
        $webrootWithoutPublic = str_replace('/Public', '', $this->config->getGameSettings()->getWebroot());

        return $webrootWithoutPublic . '/../../assets/map/';
    }
}
