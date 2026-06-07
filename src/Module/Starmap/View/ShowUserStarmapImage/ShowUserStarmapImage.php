<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowUserStarmapImage;

use InvalidArgumentException;
use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\Trait\LayerExplorationTrait;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
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
        private EncodedMapInterface $encodedMap
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
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
        $baseMtime = (int)filemtime($baseCachePath);

        $visibility = $this->getVisibility($user, $layer);
        if ($visibility['full']) {
            $this->cleanupOldMaskedCaches($layer, $user, null);
            $this->sendImage($baseCachePath, $this->buildEtag($layer, $user, $visibility['version'], $baseMtime));
        }

        $maskedCachePath = $this->getMaskedCachePath($layer, $user, $visibility['version'], $baseMtime);
        $this->ensureMaskedCacheFile($layer, $baseCachePath, $visibility['runs'], $maskedCachePath);
        $this->cleanupOldMaskedCaches($layer, $user, $maskedCachePath);
        $this->sendImage($maskedCachePath, $this->buildEtag($layer, $user, $visibility['version'], $baseMtime));
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
        $hash = hash_init('sha1');

        foreach ($runs as $run) {
            hash_update($hash, sprintf('%d:%d-%d;', $run['y'], $run['startX'], $run['endX']));
        }

        return hash_final($hash);
    }

    private function ensureBaseCacheFile(Layer $layer, string $cachePath): void
    {
        $lockHandle = fopen($cachePath . '.lock', 'c');
        if ($lockHandle !== false) {
            flock($lockHandle, LOCK_EX);
        }

        try {
            if (
                is_file($cachePath)
                && filemtime($cachePath) !== false
                && filemtime($cachePath) > time() - self::BASE_CACHE_TTL
            ) {
                return;
            }

            $tmpPath = $cachePath . '.' . getmypid() . '.tmp';
            $this->renderLayerImage($layer, $tmpPath);
            rename($tmpPath, $cachePath);
        } finally {
            if ($lockHandle !== false) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function ensureMaskedCacheFile(Layer $layer, string $baseCachePath, array $runs, string $cachePath): void
    {
        $lockHandle = fopen($cachePath . '.lock', 'c');
        if ($lockHandle !== false) {
            flock($lockHandle, LOCK_EX);
        }

        try {
            if (is_file($cachePath)) {
                return;
            }

            $tmpPath = $cachePath . '.' . getmypid() . '.tmp';
            $this->renderMaskedLayerImage($layer, $baseCachePath, $runs, $tmpPath);
            rename($tmpPath, $cachePath);
        } finally {
            if ($lockHandle !== false) {
                flock($lockHandle, LOCK_UN);
                fclose($lockHandle);
            }
        }
    }

    private function renderLayerImage(Layer $layer, string $targetPath): void
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

        /** @var array<string, \GdImage> $tileCache */
        $tileCache = [];
        foreach ($this->mapRepository->getAllOrdered($layer) as $map) {
            $this->renderMapField($image, $layer, $map, $tileCache);
        }

        imagepng($image, $targetPath, 6);
    }

    /**
     * @param array<int, array{y: int, startX: int, endX: int}> $runs
     */
    private function renderMaskedLayerImage(Layer $layer, string $baseCachePath, array $runs, string $targetPath): void
    {
        $width = $layer->getWidth() * self::CELL_SIZE;
        $height = $layer->getHeight() * self::CELL_SIZE;

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $targetImage = imagecreatetruecolor($width, $height);
        if ($targetImage === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        $background = imagecolorallocate($targetImage, 0, 0, 0);
        if ($background === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung der Hintergrundfarbe');
        }
        imagefill($targetImage, 0, 0, $background);

        $baseImage = $this->createImageFromPng($baseCachePath);
        foreach ($runs as $run) {
            $sourceX = ($run['startX'] - 1) * self::CELL_SIZE;
            $sourceY = ($run['y'] - 1) * self::CELL_SIZE;
            $copyWidth = ($run['endX'] - $run['startX'] + 1) * self::CELL_SIZE;

            imagecopy(
                $targetImage,
                $baseImage,
                $sourceX,
                $sourceY,
                $sourceX,
                $sourceY,
                $copyWidth,
                self::CELL_SIZE
            );
        }

        imagepng($targetImage, $targetPath, 6);
    }

    /**
     * @param array<string, \GdImage> $tileCache
     */
    private function renderMapField(\GdImage $targetImage, Layer $layer, Map $map, array &$tileCache): void
    {
        $imagePath = $this->getMapGraphicPath($layer, $map->getFieldType()->getType());
        if (!is_file($imagePath)) {
            $imagePath = $this->getFallbackImagePath();
        }

        $cacheTile = !$layer->isEncoded();
        $tile = $cacheTile
            ? ($tileCache[$imagePath] ??= $this->createImageFromPng($imagePath))
            : $this->createImageFromPng($imagePath);

        $targetX = ($map->getX() - 1) * self::CELL_SIZE;
        $targetY = ($map->getY() - 1) * self::CELL_SIZE;
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

    private function createImageFromPng(string $path): \GdImage
    {
        $image = imagecreatefrompng($path);
        if ($image === false) {
            throw new InvalidArgumentException(sprintf('Fehler beim Laden der Kartengrafik: %s', $path));
        }

        return $image;
    }

    private function sendImage(string $cachePath, string $etag): void
    {
        header('ETag: ' . $etag);
        header('Cache-Control: private, no-cache');

        if (($_SERVER['HTTP_IF_NONE_MATCH'] ?? '') === $etag) {
            header('HTTP/1.1 304 Not Modified');
            exit;
        }

        header('Content-Type: image/png');
        header('Content-Length: ' . filesize($cachePath));
        readfile($cachePath);
        exit;
    }

    private function buildEtag(Layer $layer, User $user, string $visibilityVersion, int $baseMtime): string
    {
        return sprintf(
            '"%s"',
            sha1(sprintf('%d:%d:%s:%d', $user->getId(), $layer->getId(), $visibilityVersion, $baseMtime))
        );
    }

    private function getBaseCachePath(Layer $layer): string
    {
        return sprintf(
            '%s/stu-user-starmap-layer-base-v1-%d-%dx%d.png',
            sys_get_temp_dir(),
            $layer->getId(),
            $layer->getWidth(),
            $layer->getHeight()
        );
    }

    private function getMaskedCachePath(Layer $layer, User $user, string $visibilityVersion, int $baseMtime): string
    {
        return sprintf(
            '%s/stu-user-starmap-mask-v1-u%d-l%d-%dx%d-%s-%d.png',
            sys_get_temp_dir(),
            $user->getId(),
            $layer->getId(),
            $layer->getWidth(),
            $layer->getHeight(),
            $visibilityVersion,
            $baseMtime
        );
    }

    private function cleanupOldMaskedCaches(Layer $layer, User $user, ?string $keepPath): void
    {
        $pattern = sprintf(
            '%s/stu-user-starmap-mask-v1-u%d-l%d-*',
            sys_get_temp_dir(),
            $user->getId(),
            $layer->getId()
        );
        $files = glob($pattern);
        if ($files === false) {
            return;
        }

        foreach ($files as $file) {
            if ($file === $keepPath || ($keepPath !== null && $file === $keepPath . '.lock')) {
                continue;
            }

            if (is_file($file)) {
                @unlink($file);
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
