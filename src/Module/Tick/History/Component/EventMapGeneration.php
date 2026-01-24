<?php

declare(strict_types=1);

namespace Stu\Module\Tick\History\Component;

use InvalidArgumentException;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\StuTime;
use Stu\Module\Logging\StuLogger;
use Stu\Module\Tick\History\HistoryTickHandlerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class EventMapGeneration implements HistoryTickHandlerInterface
{
    private const SCALE = 2;

    public function __construct(
        private readonly MapRepositoryInterface $mapRepository,
        private readonly LayerRepositoryInterface $layerRepository,
        private readonly HistoryRepositoryInterface $historyRepository,
        private readonly StuConfigInterface $config,
        private readonly EncodedMapInterface $encodedMap,
        private readonly GradientColorInterface $gradientColor,
        private readonly StuTime $stuTime
    ) {}

    #[\Override]
    public function work(): void
    {
        $mapGraphicBasePath = $this->getMapGraphicBasePath();

        foreach($this->layerRepository->findAll() as $layer) {
            $this->generateEventMapForLayer($layer, $mapGraphicBasePath);
        }
    }

    private function generateEventMapForLayer(Layer $layer, string $mapGraphicBasePath): void
    {
        $width = $layer->getWidth() * self::SCALE;
        $height = $layer->getHeight() * self::SCALE;

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        $types = [];

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        foreach ($this->mapRepository->getAllOrdered($layer) as $data) {
            if ($startY !== $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += self::SCALE;
            }

            $imagePath = $this->getMapGraphicPath($layer, $data->getFieldType()->getType(), $mapGraphicBasePath);
            $partialImage = imagecreatefrompng($imagePath);
            if ($partialImage === false) {
                throw new InvalidArgumentException('error creating partial image');
            }

            $types[$data->getFieldId()] = $partialImage;
            imagecopyresized($img, $types[$data->getFieldId()], $curx, $cury, 0, 0, self::SCALE, self::SCALE, 30, 30);
            $curx += self::SCALE;
        }

        imagefilter($img, IMG_FILTER_GRAYSCALE);
        $historyAmountsIndexed = $this->historyRepository->getAmountIndexedByLocationId(
            $layer,
            $this->stuTime->time() - TimeConstants::SEVEN_DAYS_IN_SECONDS
        );
        foreach ($historyAmountsIndexed as $data) {
            $map = $data[0];
            $locationId = $map->getId();
            $historyCount = $data['amount'];
            [$red, $green, $blue] = $this->gradientColor->calculateGradientColorRGB($historyCount, 0, 20);

            $cury = ($map->getCy() - 1) * self::SCALE;
            $curx = ($map->getCx() - 1) * self::SCALE;

            if (
                $red < 0 || $red > 255
                || $green < 0 || $green > 255
                || $blue < 0 || $blue > 255
            ) {
                throw new InvalidArgumentException(sprintf('rgb range exception, red: %d, green: %d, blue: %d', $red, $green, $blue));
            }

            StuLogger::logf("location %d has %d history entries -> rgb(%d,%d,%d)", $locationId, $historyCount, $red, $green, $blue);

            $filling = imagecreatetruecolor(self::SCALE, self::SCALE);
            $col = imagecolorallocate($filling, $red, $green, $blue);
            if (!$col) {
                throw new InvalidArgumentException(sprintf('color range exception, col: %d', $col));
            }
            imagefill($filling, 0, 0, $col);
            imagecopy($img, $filling, $curx, $cury, 0, 0, self::SCALE, self::SCALE);
        }

        $historyFolder = $this->config->getGameSettings()->getTempDir() . '/history';

        // create history folder if not exists
        if (!is_dir($historyFolder)) {
            mkdir($historyFolder, 0777, true);
        }

        //clear all resources in history folder
        $files = glob($historyFolder . '/layer_' . $layer->getId() . '.png');
        if ($files === false) {
            throw new InvalidArgumentException('error reading history folder files');
        }
        foreach ($files as $file) {
            if (is_file($file)) {
                unlink($file);
            }
        }

        StuLogger::logf("saving event map for layer %d", $layer->getId());

        imagepng(
            $img,
            sprintf(
                '%s/history/layer_%d.png',
                $this->config->getGameSettings()->getTempDir(),
                $layer->getId()
            )
        );
    }

    private function getMapGraphicBasePath(): string
    {
        $webrootWithoutPublic = str_replace("/Public", "", $this->config->getGameSettings()->getWebroot());

        return $webrootWithoutPublic . '/../../assets/map/';
    }

    private function getMapGraphicPath(Layer $layer, int $fieldType, string $mapGraphicBasePath): string
    {
        if ($layer->isEncoded()) {
            return $mapGraphicBasePath . $this->encodedMap->getEncodedMapPath($fieldType, $layer);
        }

        return $mapGraphicBasePath . $layer->getId() . "/" . $fieldType . '.png';
    }
}
