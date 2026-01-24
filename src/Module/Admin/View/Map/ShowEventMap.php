<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use InvalidArgumentException;
use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Logging\StuLogger;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Entity\Layer;
use Stu\Orm\Repository\HistoryRepositoryInterface;

final class ShowEventMap implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_EVENT_MAP';

    public function __construct(
        private readonly MapRepositoryInterface $mapRepository,
        private readonly LayerRepositoryInterface $layerRepository,
        private readonly HistoryRepositoryInterface $historyRepository,
        private readonly StuConfigInterface $config,
        private readonly EncodedMapInterface $encodedMap
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $layerId = request::getIntFatal('layerid');

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            $game->getInfo()->addInformation(sprintf('layerId %d does not exist', $layerId));
            return;
        }

        $types = [];
        $width = $layer->getWidth() * 15;
        $height = $layer->getHeight() * 15;

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $historyAmountsIndexed = $this->historyRepository->getAmountIndexedByLocationId($layer);

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;


        foreach ($this->mapRepository->getAllOrdered($layer) as $data) {
            if ($startY !== $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += 15;
            }

            $historyCount = $historyAmountsIndexed[$data->getId()] ?? 0;
            if ($historyCount > 0) {
                // Calculate RGB values based on historyCount using a logarithmic approach
                $logCount = 0; //log($historyCount);
                $red = 0;
                $green = 0;
                $blue = 0;

                if ($logCount <= log(5)) {
                    // Green to Yellow
                    $green = 255;
                    $red = (int)(255 * ($logCount / log(5)));
                } elseif ($logCount <= log(10)) {
                    // Yellow to Orange
                    $red = 255;
                    $green = (int)(255 * (1 - (($logCount - log(5)) / (log(10) - log(5)))));
                } elseif ($logCount <= log(20)) {
                    // Orange to Red
                    $red = 255;
                    $green = 0;
                    $blue = (int)(255 * (($logCount - log(10)) / (log(20) - log(10))));
                } elseif ($logCount <= log(40)) {
                    // Red to Purple
                    $red = 255;
                    $blue = (int)(255 * (($logCount - log(20)) / (log(40) - log(20))));
                } else {
                    // Beyond 40, set to Purple
                    $red = 128;
                    $blue = 128;
                }

                if (
                    $red < 0 || $red > 255
                    || $green < 0 || $green > 255
                    || $blue < 0 || $blue > 255
                ) {
                    throw new InvalidArgumentException(sprintf('rgb range exception, red: %d, green: %d, blue: %d', $red, $green, $blue));
                }

                StuLogger::logf("location %d has %d history entries -> rgb(%d,%d,%d)", $data->getId(), $historyCount, $red, $green, $blue);

                $filling = imagecreatetruecolor(15, 15);
                $col = imagecolorallocate($filling, $red, $green, $blue);
                if (!$col) {
                    throw new InvalidArgumentException(sprintf('color range exception, col: %d', $col));
                }
                imagefill($filling, 0, 0, $col);
                imagecopy($img, $filling, $curx, $cury, 0, 0, 15, 15);
                $curx += 15;
                continue;
            }

            $imagePath = $this->getMapGraphicPath($layer, $data->getFieldType()->getType());
            $partialImage = imagecreatefrompng($imagePath);
            if ($partialImage === false) {
                throw new InvalidArgumentException('error creating partial image');
            }
            $types[$data->getFieldId()] = $partialImage;
            imagecopyresized($img, $types[$data->getFieldId()], $curx, $cury, 0, 0, 15, 15, 30, 30);
            $curx += 15;
        }

        header("Content-type: image/png");
        imagepng($img);
        exit;
    }

    private function getMapGraphicPath(Layer $layer, int $fieldType): string
    {
        $webrootWithoutPublic = str_replace("/Public", "", $this->config->getGameSettings()->getWebroot());

        if ($layer->isEncoded()) {
            return $webrootWithoutPublic . '/../../assets/map/' . $this->encodedMap->getEncodedMapPath($fieldType, $layer);
        }

        return $webrootWithoutPublic . '/../../assets/map/' . $layer->getId() . "/" . $fieldType . '.png';
    }
}
