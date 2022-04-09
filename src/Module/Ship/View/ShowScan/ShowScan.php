<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowScan;

use request;

use Stu\Component\Game\GameEnum;
use Stu\Module\Ship\Lib\PositionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class ShowScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SCAN';

    private ShipLoaderInterface $shipLoader;

    private PositionCheckerInterface $positionChecker;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PositionCheckerInterface $positionChecker,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->positionChecker = $positionChecker;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $shipArray = $this->shipLoader->getByIdAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $ship = $shipArray[$shipId];
        $target = $shipArray[$targetId];
        if ($target === null) {
            return;
        }
        $game->setPageTitle(_('Scan'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_scan');
        if (!$this->positionChecker->checkPosition($ship, $target)) {
            $game->addInformation(_('Das Schiff befindet sich nicht in diesem Sektor'));
            return;
        }

        if ($target->getCloakState()) {
            return;
        }

        if ($target->getDatabaseId()) {
            $game->checkDatabaseItem($target->getDatabaseId());
        }
        if ($target->getRump()->getDatabaseId()) {
            $game->checkDatabaseItem($target->getRump()->getDatabaseId());
        }

        $href = sprintf(_('ship.php?SHOW_SHIP=1&id=%d'), $target->getId());

        $this->privateMessageSender->send(
            $game->getUser()->getId(),
            $target->getUser()->getId(),
            sprintf(
                _('Die %s von Spieler %s hat %s %s bei %s gescannt.'),
                $ship->getName(),
                $game->getUser()->getName(),
                $target->isBase() ? 'deine Station' : 'dein Schiff',
                $target->getName(),
                $target->getSectorString()
            ),
            $target->isBase() ?  PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $game->setTemplateVar('targetShip', $target);
        $game->setTemplateVar('SHIP', $ship);
    }
}
