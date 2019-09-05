<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowOverall;

use AccessViolation;
use MapField;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Orm\Repository\MapBorderTypeRepositoryInterface;
use Stu\Orm\Repository\MapFieldTypeRepositoryInterface;

final class ShowOverall implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_OVERALL';

    private $mapBorderTypeRepository;

    private $mapFieldTypeRepository;

    public function __construct(
        MapBorderTypeRepositoryInterface $mapBorderTypeRepository,
        MapFieldTypeRepositoryInterface $mapFieldTypeRepository
    ) {
        $this->mapBorderTypeRepository = $mapBorderTypeRepository;
        $this->mapFieldTypeRepository = $mapFieldTypeRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        if (!$game->isAdmin()) {
            throw new AccessViolation();
        }
        $types = [];
        $img = imagecreatetruecolor(MAP_MAX_X * 15, MAP_MAX_Y * 15);

        // mapfields
        $startY = 1;
        $cury = 0;
        $curx = 0;

        $map = MapField::getFieldsBy('cx BETWEEN 1 AND ' . MAP_MAX_X . ' AND cy BETWEEN 1 AND ' . MAP_MAX_Y);
        while ($data = mysqli_fetch_assoc($map)) {
            if ($startY != $data['cy']) {
                $startY = $data['cy'];
                $curx = 0;
                $cury += 15;
            }
            if ($data['bordertype_id'] > 0) {
                $border = imagecreatetruecolor(15, 15);
                $var = '#' . $this->mapBorderTypeRepository->find((int) $data['bordertype_id'])->getColor();
                $arr = sscanf($var, '#%2x%2x%2x');
                $col = imagecolorallocate($border, $arr[0], $arr[1], $arr[2]);
                imagefill($border, 0, 0, $col);
                imagecopy($img, $border, $curx, $cury, 0, 0, 15, 15);
                $curx += 15;
                continue;
            }
            if (!array_key_exists($data['field_id'], $types)) {
                $maptype = $this->mapFieldTypeRepository->find((int) $data['field_id']);
                $types[$data['field_id']] = imagecreatefromgif(APP_PATH . 'src/assets/map/' . $maptype->getType() . '.gif');
            }
            imagecopyresized($img, $types[$data['field_id']], $curx, $cury, 0, 0, 15, 15, 30, 30);
            $curx += 15;
        }
        header("Content-type: image/png");
        imagepng($img);
        imagedestroy($img);
        exit;
    }
}