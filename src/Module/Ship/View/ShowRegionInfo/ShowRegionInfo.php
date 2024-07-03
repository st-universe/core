<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowRegionInfo;

use Override;
use request;
use Stu\Exception\AccessViolation;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowRegionInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_REGION_INFO';

    public function __construct(private ShipLoaderInterface $shipLoader)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $regionId = request::getIntFatal('region');

        $mapField = $ship->getCurrentMapField();

        $mapRegion = $mapField->getMapRegion();
        $adminRegion = $mapField->getAdministratedRegion();

        if ($mapRegion === null && $adminRegion === null) {
            throw new AccessViolation();
        }

        if ($mapRegion !== null && $mapRegion->getId() === $regionId) {
            $region = $mapRegion;
        } elseif ($adminRegion !== null &&  $adminRegion->getId() === $regionId) {
            $region = $adminRegion;
        } else {
            throw new AccessViolation();
        }

        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/regioninfo');
        $game->setPageTitle(sprintf('Details: %s', $region->getDescription()));

        $game->setTemplateVar('REGION', $region);
        $game->setTemplateVar('SHIP', $ship);
    }
}
