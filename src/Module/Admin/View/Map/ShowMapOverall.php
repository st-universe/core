<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use InvalidArgumentException;
use Override;
use request;
use Stu\Component\Map\EncodedMapInterface;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Entity\Layer;


final class ShowMapOverall implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MAP_OVERALL';

    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private StuConfigInterface $config,
        private EncodedMapInterface $encodedMap
    ) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $layerId = request::getIntFatal('layerid');

        $layer = $this->layerRepository->find($layerId);
        if ($layer === null) {
            $game->addInformation(sprintf('layerId %d does not exist', $layerId));
            return;
        }

        $types = [];
        $width = $layer->getWidth() * 15;
        $height = $layer->getHeight() * 15;

        if ($width < 1 || $height < 1) {
            throw new InvalidArgumentException('Ungültige Dimensionen für die Bilderstellung');
        }

        $img = imagecreatetruecolor($width, $height);
        if ($img === false) {
            throw new InvalidArgumentException('Fehler bei Erstellung von true color image');
        }

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        foreach ($this->mapRepository->getAllOrdered($layerId) as $data) {
            if ($startY !== $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += 15;
            }
            $borderType = $data->getMapBorderType();
            if ($borderType !== null) {
                $var = $borderType->getColor();

                if (!preg_match('/^#([0-9a-fA-F]{2})([0-9a-fA-F]{2})([0-9a-fA-F]{2})$/', $var, $matches)) {
                    throw new InvalidArgumentException(sprintf('Invalid color format: %s', $var));
                }

                $red = (int) hexdec($matches[1]);
                $green = (int) hexdec($matches[2]);
                $blue = (int) hexdec($matches[3]);

                if (
                    $red < 0 || $red > 255
                    || $green < 0 || $green > 255
                    || $blue < 0 || $blue > 255
                ) {
                    throw new InvalidArgumentException(sprintf('rgb range exception, red: %d, green: %d, blue: %d', $red, $green, $blue));
                }
                $border = imagecreatetruecolor(15, 15);
                $col = imagecolorallocate($border, $red, $green, $blue);
                if (!$col) {
                    throw new InvalidArgumentException(sprintf('color range exception, col: %d', $col));
                }
                imagefill($border, 0, 0, $col);
                imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
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
        imagedestroy($img);
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
