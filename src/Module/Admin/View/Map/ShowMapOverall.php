<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Noodlehaus\ConfigInterface;
use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapOverall implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_MAP_OVERALL';

    private MapBorderTypeRepositoryInterface $mapBorderTypeRepository;

    private MapFieldTypeRepositoryInterface $mapFieldTypeRepository;

    private MapRepositoryInterface $mapRepository;

    private ConfigInterface $config;

    public function __construct(
        MapBorderTypeRepositoryInterface $mapBorderTypeRepository,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository,
        MapRepositoryInterface $mapRepository,
        ConfigInterface $config
    ) {
        $this->mapBorderTypeRepository = $mapBorderTypeRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
        $this->mapRepository = $mapRepository;
        $this->config = $config;
    }

    public function handle(GameControllerInterface $game): void
    {
        $types = [];
        $img = imagecreatetruecolor(MapEnum::MAP_MAX_X * 15, MapEnum::MAP_MAX_Y * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        $webrootWithoutPublic = str_replace("/Public", "", $this->config->get('game.webroot'));

        foreach ($this->mapRepository->getAllOrdered() as $data) {
            if ($startY != $data->getCy()) {
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
                $webrootWithoutPublic . '/../../assets/map/' . $data->getFieldType()->getType() . '.png'
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
