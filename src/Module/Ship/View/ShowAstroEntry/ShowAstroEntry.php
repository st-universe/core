<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAstroEntry;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowAstroEntry implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ASTRO_ENTRY';

    private ShipLoaderInterface $shipLoader;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    private MapRepositoryInterface $mapRepository;

    private StarSystemMapRepositoryInterface $starSystemMapRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        AstroEntryRepositoryInterface $astroEntryRepository,
        MapRepositoryInterface $mapRepository,
        StarSystemMapRepositoryInterface $starSystemMapRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->astroEntryRepository = $astroEntryRepository;
        $this->mapRepository = $mapRepository;
        $this->starSystemMapRepository = $starSystemMapRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $astroEntry = $this->astroEntryRepository->getByShipLocation($ship);
        if ($astroEntry === null) {
            return;
        }

        $game->setPageTitle("anzufliegende Messpunkte");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/astroentries');

        $system = $ship->getSystem() ?? $ship->isOverSystem();
        $repository = $system !== null ? $this->starSystemMapRepository : $this->mapRepository;

        $game->setTemplateVar(
            'FIELDS',
            array_map(
                fn (int $id) => $repository->find($id),
                unserialize($astroEntry->getFieldIds())
            )
        );
    }
}
