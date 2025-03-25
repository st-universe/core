<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\View\ShowRegionInfo;

use Override;
use request;
use RuntimeException;
use Stu\Exception\AccessViolationException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Entity\MapInterface;

final class ShowRegionInfo implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_REGION_INFO';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(private SpacecraftLoaderInterface $spacecraftLoader) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $regionId = request::getIntFatal('regionid');

        $mapField = $ship->getLocation();
        if (!$mapField instanceof MapInterface) {
            throw new RuntimeException('region info only available for map fields');
        }

        $mapRegion = $mapField->getMapRegion();
        $adminRegion = $mapField->getAdministratedRegion();

        if ($mapRegion === null && $adminRegion === null) {
            throw new AccessViolationException();
        }

        if ($mapRegion !== null && $mapRegion->getId() === $regionId) {
            $region = $mapRegion;
        } elseif ($adminRegion !== null &&  $adminRegion->getId() === $regionId) {
            $region = $adminRegion;
        } else {
            throw new AccessViolationException();
        }

        $game->setMacroInAjaxWindow('html/ship/regioninfo.twig');
        $game->setPageTitle(sprintf('Details: %s', $region->getDescription()));

        $game->setTemplateVar('REGION', $region);
        $game->setTemplateVar('SHIP', $ship);
    }
}
