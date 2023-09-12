<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use request;
use Stu\Module\Config\StuConfigInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\LayerRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapOverall implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MAP_OVERALL';

    private MapRepositoryInterface $mapRepository;

    private LayerRepositoryInterface $layerRepository;

    private StuConfigInterface $config;

    public function __construct(
        MapRepositoryInterface $mapRepository,
        LayerRepositoryInterface $layerRepository,
        StuConfigInterface $config
    ) {
        $this->mapRepository = $mapRepository;
        $this->layerRepository = $layerRepository;
        $this->config = $config;
    }

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
                $border = imagecreatetruecolor(15, 15);
                $var = $borderType->getColor();
                $arr = sscanf($var, '#%2x%2x%2x');
                $col = imagecolorallocate($border, $arr[0], $arr[1], $arr[2]);
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
