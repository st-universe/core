<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAstroEntry;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\AstroEntryLibInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\MapRepositoryInterface;
use Stu\Orm\Repository\StarSystemMapRepositoryInterface;

final class ShowAstroEntry implements ViewControllerInterface
{
    public const string VIEW_IDENTIFIER = 'SHOW_ASTRO_ENTRY';

    public function __construct(
        private ShipLoaderInterface $shipLoader,
        private MapRepositoryInterface $mapRepository,
        private StarSystemMapRepositoryInterface $starSystemMapRepository,
        private AstroEntryLibInterface $astroEntryLib
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId,
            true,
            false
        );

        $isSystem = request::getIntFatal('isSystem');

        $astroEntry = $this->astroEntryLib->getAstroEntryByShipLocation($ship, $isSystem === 1);
        if ($astroEntry === null) {
            return;
        }

        $game->setPageTitle("anzufliegende Messpunkte");
        $game->setMacroInAjaxWindow('html/ship/astroentries.twig');

        $system = null;
        if ($isSystem !== 0) {
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
