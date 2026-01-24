<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use InvalidArgumentException;
use request;
use Stu\Component\Game\TimeConstants;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Lib\ModuleScreen\GradientColorInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\StuTime;
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
        private readonly EncodedMapInterface $encodedMap,
        private readonly GradientColorInterface $gradientColor,
        private readonly StuTime $stuTime
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

        $scale = request::getInt('scale', 15);
        $grayScale = request::has('grayscale');
        $width = $layer->getWidth() * $scale;
        $height = $layer->getHeight() * $scale;

        if ($width < 1 || $height < 1 || $scale < 1) {
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
                $cury += $scale;
            }

            $imagePath = $this->getMapGraphicPath($layer, $data->getFieldType()->getType());
            $partialImage = imagecreatefrompng($imagePath);
            if ($partialImage === false) {
                throw new InvalidArgumentException('error creating partial image');
            }

            $types[$data->getFieldId()] = $partialImage;
            imagecopyresized($img, $types[$data->getFieldId()], $curx, $cury, 0, 0, $scale, $scale, 30, 30);
            $curx += $scale;
        }

        if ($grayScale) {
            imagefilter($img, IMG_FILTER_GRAYSCALE);
        }

        $historyAmountsIndexed = $this->historyRepository->getAmountIndexedByLocationId(
            $layer,
            $this->stuTime->time() - TimeConstants::SEVEN_DAYS_IN_SECONDS
        );
        foreach ($historyAmountsIndexed as $data) {
            $map = $data[0];
            $locationId = $map->getId();
            $historyCount = $data['amount'];
            $rgb = $this->gradientColor->calculateGradientColorRGB($historyCount, 0, 20);
            $red = $rgb[0];
            $green = $rgb[1];
            $blue = $rgb[2];

            $cury = ($map->getCy() - 1) * $scale;
            $curx = ($map->getCx() - 1) * $scale;

            if (
                $red < 0 || $red > 255
                || $green < 0 || $green > 255
                || $blue < 0 || $blue > 255
            ) {
                throw new InvalidArgumentException(sprintf('rgb range exception, red: %d, green: %d, blue: %d', $red, $green, $blue));
            }

            StuLogger::logf("location %d has %d history entries -> rgb(%d,%d,%d)", $locationId, $historyCount, $red, $green, $blue);

            $filling = imagecreatetruecolor($scale, $scale);
            $col = imagecolorallocate($filling, $red, $green, $blue);
            if (!$col) {
                throw new InvalidArgumentException(sprintf('color range exception, col: %d', $col));
            }
            imagefill($filling, 0, 0, $col);
            imagecopy($img, $filling, $curx, $cury, 0, 0, $scale, $scale);
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
