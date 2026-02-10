<?php

declare(strict_types=1);

namespace Stu\Module\Tick\History\Component;

use GdImage;
use Imagick;
use InvalidArgumentException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Logging\LogTypeEnum;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\History\HistoryTickHandlerInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\AnomalyRepositoryInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;

final class IonStormMapGeneration implements HistoryTickHandlerInterface
{
    private const MAX_FRAMES = 168; // 7 days * 24 hours

    public function __construct(
        private readonly AnomalyRepositoryInterface $anomalyRepository,
        private readonly LayerRepositoryInterface $layerRepository,
        private readonly StuConfigInterface $config
    ) {}

    #[\Override]
    public function work(): void
    {
        StuLogger::log("    starting IonStormMapGeneration", LogTypeEnum::TICK);

        $mapGraphicBasePath = $this->getMapGraphicBasePath();
        $historyFolder = $this->config->getGameSettings()->getTempDir() . '/history';

        // create history folder if not exists
        if (!is_dir($historyFolder)) {
            StuLogger::log("    creating history folder", LogTypeEnum::TICK);
            mkdir($historyFolder, 0o777, true);
        }

        foreach ($this->layerRepository->findAll() as $layer) {
            $this->generateIonStormMapForLayer($layer, $historyFolder);
        }
    }

    private function generateIonStormMapForLayer(Layer $layer, string $historyFolder): void
    {
        StuLogger::log("    generating IonStormMap for Layer " . $layer->getId(), LogTypeEnum::TICK);

        $width = $layer->getWidth() * 2;
        $height = $layer->getHeight() * 2;
        if ($width <= 0 || $height <= 0) {
            throw new InvalidArgumentException('Ungültige Bildgröße für Layer ' . $layer->getId());
        }

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        $lightBlue = imagecolorallocate($img, 173, 216, 230);
        if ($lightBlue === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung der Farbe');
        }

        $fillColor = imagecolorallocate($img, 0, 0, 0);
        if ($fillColor === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung der Farbe');
        }
        imagefill($img, 0, 0, $fillColor);

        foreach ($this->anomalyRepository->getLocationsWithIonstormAnomalies() as $location) {
            $curx = ($location->getCx() - 1) * 2;
            $cury = ($location->getCy() - 1) * 2;

            imagefilledrectangle(
                $img,
                (int)$curx,
                (int)$cury,
                (int)($curx + 2),
                (int)($cury + 2),
                $lightBlue
            );
        }

        $gifPath = sprintf('%s/ionstorm_map_layer_%d.gif', $historyFolder, $layer->getId());
        if (file_exists($gifPath)) {
            $this->editExistingGif($gifPath, $img);
        } else {
            $this->createNewGif($gifPath, $img);
        }
    }

    private function createNewGif(string $gifPath, GdImage $img): void
    {
        $gif = new Imagick();
        $gif->setFormat('gif');
        $this->addFrameAndWriteGifToFile($gifPath, $img, $gif);
    }

    private function editExistingGif(string $gifPath, GdImage $img): void
    {
        $gif = new Imagick($gifPath);
        if ($gif->getNumberImages() >= self::MAX_FRAMES) {
            $gif->removeImage();
        }
        $this->addFrameAndWriteGifToFile($gifPath, $img, $gif);
    }

    private function addFrameAndWriteGifToFile(string $gifPath, GdImage $img, Imagick $gif): void
    {
        $frame = new Imagick();
        $frame->readImageBlob($this->getImageBlob($img));
        $frame->setImageDelay(25); // 1/4 second delay

        $gif->addImage($frame);
        $gif = $gif->coalesceImages();
        $gif->optimizeImageLayers();
        $gif->setFirstIterator();
        $gif->setImageIterations(0); // loop indefinitely
        $gif->writeImages($gifPath, true);
    }

    private function getImageBlob(GdImage $img): string
    {
        ob_start();
        imagegif($img);
        $buffer = ob_get_clean();
        if ($buffer === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung des Bildes');
        }
        return $buffer;
    }

    private function getMapGraphicBasePath(): string
    {
        $webrootWithoutPublic = str_replace("/Public", "", $this->config->getGameSettings()->getWebroot());

        return $webrootWithoutPublic . '/../../assets/map/';
    }
}