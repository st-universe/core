<?php

declare(strict_types=1);

namespace Stu\Module\Starmap\View\ShowByPosition;

use request;
use Stu\Component\Map\MapEnum;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use UserYRow;

final class ShowByPosition implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_STARMAP_POSITION';
    private const FIELDS_PER_SECTION = 20;

    private ShowByPositionRequestInterface $showByPositionRequest;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ShowByPositionRequestInterface $showByPositionRequest,
        ShipLoaderInterface $shipLoader
    ) {
        $this->showByPositionRequest = $showByPositionRequest;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(request::getIntFatal('id'), $userId, true);

        $xCoordinate = $this->showByPositionRequest->getXCoordinate();
        $yCoordinate = $this->showByPositionRequest->getYCoordinate();

        if ($ship->getMapCX() !== $xCoordinate || $ship->getMapCY() !== $yCoordinate) {
            throw new AccessViolation(sprintf(
                'userId %d tried to show starMap on invalid position with mapCX: %d, mapCY: %d, but shipId %d is on mapCX: %d, mapCY: %d',
                $userId,
                $xCoordinate,
                $yCoordinate,
                $ship->getId(),
                $ship->getMapCX(),
                $ship->getMapCY()
            ));
        }

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
        $game->setTemplateVar('HAS_NAV_LEFT', $xCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_RIGHT', $xCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_X);
        $game->setTemplateVar('HAS_NAV_UP', $yCoordinate > 1);
        $game->setTemplateVar('HAS_NAV_DOWN', $yCoordinate * static::FIELDS_PER_SECTION < MapEnum::MAP_MAX_Y);

        $game->setTemplateVar(
            'NAV_UP',
            [
                'x' => $xCoordinate,
                'y' => $yCoordinate - 1
            ]
        );
        $game->setTemplateVar(
            'NAV_DOWN',
            [
                'x' => $xCoordinate,
                'y' => $yCoordinate + 1
            ]
        );
        $game->setTemplateVar(
            'NAV_LEFT',
            [
                'x' => $xCoordinate - 1,
                'y' => $yCoordinate
            ]
        );
        $game->setTemplateVar(
            'NAV_RIGHT',
            [
                'x' => $xCoordinate + 1,
                'y' => $yCoordinate
            ]
        );
    }
}
