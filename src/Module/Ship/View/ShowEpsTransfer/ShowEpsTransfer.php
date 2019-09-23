<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowEpsTransfer;

use request;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ShowEpsTransfer implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_ETRANSFER';

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
        if ($ship->canInteractWith($target) === false) {
            // @todo ships cant interact
            return;
        }

        $game->setPageTitle("Energietransfer");
        $game->setTemplateFile('html/ajaxwindow.xhtml');
        $game->setMacro('html/shipmacros.xhtml/show_ship_etransfer');

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
    }
}
