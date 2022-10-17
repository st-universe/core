<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use Stu\Component\Map\MapEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use UserYRow;

final class ShowByPosition implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';
    private const FIELDS_PER_SECTION = 20;

    private ShowByPositionRequestInterface $showByPositionRequest;

    public function __construct(
        ShowByPositionRequestInterface $showByPositionRequest
    ) {
        $this->showByPositionRequest = $showByPositionRequest;
    }

    public function handle(GameControllerInterface $game): void
    {
        $xCoordinate = $this->showByPositionRequest->getXCoordinate();
        $yCoordinate = $this->showByPositionRequest->getYCoordinate();

        $maxx = $xCoordinate * self::FIELDS_PER_SECTION;
        $minx = $maxx - self::FIELDS_PER_SECTION + 1;
        $maxy = $yCoordinate * self::FIELDS_PER_SECTION;
        $miny = $maxy - self::FIELDS_PER_SECTION + 1;

        $fields = [];
        foreach (range($miny, $maxy) as $value) {
            $fields[] = new UserYRow($game->getUser(), $value, $minx, $maxx);
        }

        $game->setMacroInAjaxWindow('html/macros.xhtml/starmap');

        $game->setTemplateVar('HEAD_ROW', range($minx, $maxx));
        $game->setTemplateVar('MAP_FIELDS', $fields);

        if ($yCoordinate > 1) {
            $game->setTemplateVar(
                'NAV_UP',
                $this->constructPath($xCoordinate, $yCoordinate - 1)
            );
        }
        if ($yCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_Y) {
            $game->setTemplateVar(
                'NAV_DOWN',
                $this->constructPath($xCoordinate, $yCoordinate + 1)
            );
        }
        if ($xCoordinate > 1) {
            $game->setTemplateVar(
                'NAV_LEFT',
                $this->constructPath($xCoordinate - 1, $yCoordinate)
            );
        }
        if ($xCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_X) {
            $game->setTemplateVar(
                'NAV_RIGHT',
                $this->constructPath($xCoordinate + 1, $yCoordinate)
            );
        }
    }

    private function constructPath(int $x, int $y): string
    {
        return sprintf(
            'starmap.php?%s=1&x=%d&y=%d',
            self::VIEW_IDENTIFIER,
            $x,
            $y
        );
    }
}
