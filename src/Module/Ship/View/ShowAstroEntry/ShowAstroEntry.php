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

        $isSystem = request::getIntFatal('isSystem');

        $astroEntry = $this->astroEntryRepository->getByShipLocation($ship, $isSystem === 1);
        if ($astroEntry === null) {
            return;
        }

        $game->setPageTitle("anzufliegende Messpunkte");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/astroentries');

        $system = null;
        if ($isSystem) {
            $system = $ship->getSystem();
            if ($system === null) {
                $system = $ship->isOverSystem();
            }
        }
        $repository = $system !== null ? $this->starSystemMapRepository : $this->mapRepository;

        $fieldIdArray = unserialize($astroEntry->getFieldIds());

        $game->setTemplateVar(
            'FIELDS',
            array_map(
                fn (int $id) => $repository->find($id),
                $fieldIdArray
            )
        );

        $game->setTemplateVar('GRID_COLUMNS', $this->getGridColumns(count($fieldIdArray)));
    }

    private function getGridColumns(int $fieldCount): string
    {
        $columnCount = $fieldCount < 10 ? 1 : (int)sqrt($fieldCount);
        $width = $columnCount === 1 ? '100%' : '60px';

        return implode(' ', array_fill(0, $columnCount, $width));
    }
}
