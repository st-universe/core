<?php

declare(strict_types=1);

namespace Stu\Module\Admin\View\Map;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowMapInfluenceAreas implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_INFLUENCE_AREAS';

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        MapRepositoryInterface $mapRepository
    ) {
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $img = imagecreatetruecolor(MapEnum::MAP_MAX_X * 15, MapEnum::MAP_MAX_Y * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        foreach ($this->mapRepository->getAllOrdered() as $data) {
            if ($startY != $data->getCy()) {
                $startY = $data->getCy();
                $curx = 0;
                $cury += 15;
            }

            $id = $data->getInfluenceAreaId();

            $rest = $id % 20;

            $border = imagecreatetruecolor(15, 15);
            if ($data->getSystem()) {
                $col = imagecolorallocate($border, 255, 255, 255);
            } else {
                $col = imagecolorallocate($border, $rest * 10, $rest * 10, $rest * 10);
            }
            imagefill($border, 0, 0, $col);
            imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
            $curx += 15;
            continue;
        }
        header("Content-type: image/png");
        imagepng($img);
        imagedestroy($img);
        exit;
    }
}
