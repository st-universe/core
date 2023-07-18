<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowAstroEntry;

use request;

use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\AstroEntryRepositoryInterface;

final class ShowAstroEntry implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ASTRO_ENTRY';

    private ShipLoaderInterface $shipLoader;

    private AstroEntryRepositoryInterface $astroEntryRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        AstroEntryRepositoryInterface $astroEntryRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->astroEntryRepository = $astroEntryRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $system = $ship->getSystem() ?? $ship->isOverSystem();
        $entry = $this->astroEntryRepository->getByUserAndSystem($ship->getUser()->getId(), $system->getId());

        $game->setPageTitle("anzufliegende Messpunkte");
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/astroentry');

        $game->setTemplateVar('ENTRY', $entry);
    }
}
