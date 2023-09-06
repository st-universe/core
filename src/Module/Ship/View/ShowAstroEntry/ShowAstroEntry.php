<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAstroEntry;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\Map;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;

final class ShowAstroEntry implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ASTRO_ENTRY';

    private ShipLoaderInterface $shipLoader;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private MapRepositoryInterface $mapRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        MapRepositoryInterface $mapRepository,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->mapRepository = $mapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getMap() != null) {
            if ($ship->getMap()->getMapRegion() != null) {
                $entry = $this->astroEntryRepository->getByUserAndRegion($ship->getUser()->getId(), $ship->getMap()->getMapRegion()->getId());
                $game->setPageTitle("anzufliegende Messpunkte");
                $game->setMacroInAjaxWindow('html/shipmacros.xhtml/astroregionentry');
                if ($entry !== null) {
                    if ($entry->getRegionFields() !== null) {
                        $array = unserialize($entry->getRegionFields());
                        $results = [];
                        foreach ($array  as $item) {
                            if (is_array($item) && isset($item['id']) && is_int($item['id'])) {
                                $result = $this->mapRepository->getById($item['id']);
                                $results[] = $result;
                            }
                        }

                        $game->setTemplateVar('ENTRY', $results);
                    }
                }
            }
        }

        $system = $ship->getSystem() ?? $ship->isOverSystem();

        if ($system != null) {
            $entry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser()->getId(), $system->getId());

            $game->setPageTitle("anzufliegende Messpunkte");
            $game->setMacroInAjaxWindow('html/shipmacros.xhtml/astroentry');

            $game->setTemplateVar('ENTRY', $entry);
        }
    }
}
