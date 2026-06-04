<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map\LiveMap;

use InvalidArgumentException;
use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowLiveMapImage implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ADMIN_LIVE_MAP_IMAGE';
    public const int CELL_SIZE = 30;

    private const int CACHE_TTL = 1800;

    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private StuConfigInterface $config,
        private EncodedMapInterface $encodedMap
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            header('HTTP/1.1 403 Forbidden');
            exit;
        }

        $layerId = request::getIntFatal('layerid');
        $layer = $this->layerRepository->find($layerId);
        if (!$layer instanceof Layer) {
            header('HTTP/1.1 404 Not Found');
            exit;
        }

        $cachePath = $this->getCachePath($layer);
        $refresh = request::getInt('refresh') === 1;

        $this->ensureCacheFile($layer, $cachePath, $refresh);
        $this->sendImage($cachePath);
    }

    private function ensureCacheFile(Layer $layer, string $cachePath, bool $refresh): void
    {
        $lockHandle = fopen($cachePath . '.lock', 'c');
        if ($lockHandle !== false) {
            flock($lockHandle, LOCK_EX);
        }

        try {
            if (
                !$refresh
                && is_file($cachePath)
                && filemtime($cachePath) !== false
                && filemtime($cachePath) > time() - self::CACHE_TTL
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
        } else {
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

    }

    private function createImageFromPng(string $path): \GdImage
    {
        $image = imagecreatefrompng($path);
        if ($image === false) {
            throw new InvalidArgumentException(sprintf('Fehler beim Laden der Kartengrafik: %s', $path));
        }

        return $image;
    }

    private function sendImage(string $cachePath): void
    {
        header('Content-Type: image/png');
        header('Cache-Control: private, max-age=300');
        header('Content-Length: ' . filesize($cachePath));
        readfile($cachePath);
        exit;
    }

    private function getCachePath(Layer $layer): string
    {
        return sprintf(
            '%s/stu-admin-live-map-layer-base-v2-%d-%dx%d.png',
            sys_get_temp_dir(),
            $layer->getId(),
            $layer->getWidth(),
            $layer->getHeight()
        );
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
