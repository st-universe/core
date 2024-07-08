<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Override;
use request;
use RuntimeException;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapOverall implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_MAP_OVERALL';

    public function __construct(
        private MapRepositoryInterface $mapRepository,
        private LayerRepositoryInterface $layerRepository,
        private StuConfigInterface $config
    ) {
    }

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
        $img = imagecreatetruecolor($layer->getWidth() * 15, $layer->getHeight() * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        $webrootWithoutPublic = str_replace("/Public", "", $this->config->getGameSettings()->getWebroot());

        foreach ($this->mapRepository->getAllOrdered($layerId) as $data) {
            if ($startY !== $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += 15;
            }
            $borderType = $data->getMapBorderType();
            if ($borderType !== null) {
                $var = $borderType->getColor();
                $arr = sscanf($var, '#%2x%2x%2x');
                $red = $arr[0];
                $green = $arr[1];
                $blue = $arr[2];
                if (
                    !$red || $red < 1 || $red > 255
                    || !$green || $green < 1 || $green > 255
                    || !$blue || $blue < 1 || $blue > 255
                ) {
                    throw new RuntimeException(sprintf('rgb range exception, red: %d, green: %d, blue: %d', $red, $green, $blue));
                }
                $border = imagecreatetruecolor(15, 15);
                $col = imagecolorallocate($border, $red, $green, $blue);
                if (!$col) {
                    throw new RuntimeException(sprintf('color range exception, col: %d', $col));
                }
                imagefill($border, 0, 0, $col);
                imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
                $curx += 15;
                continue;
            }

            $types[$data->getFieldId()] = imagecreatefrompng(
                $webrootWithoutPublic . '/../../assets/map/' . $data->getLayer()->getId() . "/" . $data->getFieldType()->getType() . '.png'
            );
            imagecopyresized($img, $types[$data->getFieldId()], $curx, $cury, 0, 0, 15, 15, 30, 30);
            $curx += 15;
        }
        header("Content-type: image/png");
        imagepng($img);
        imagedestroy($img);
        exit;
    }
}
