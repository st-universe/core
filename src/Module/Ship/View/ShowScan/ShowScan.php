<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowScan;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SCAN';

    private $shipLoader;

    private $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = $this->shipRepository->find(request::getIntFatal('target'));
        if ($target === null) {
            return;
        }
        $game->setPageTitle(_('Scan'));
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/show_ship_scan');
        if (!checkPosition($ship, $target)) {
            $game->addInformation(_('Das Schiff befindet sich nicht in diesem Sektor'));
            return;
        }

        if ($target->getDatabaseId()) {
            $game->checkDatabaseItem($target->getDatabaseId());
        }
        if ($target->getRump()->getDatabaseId()) {
            $game->checkDatabaseItem($target->getRump()->getDatabaseId());
        }

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
    }
}
