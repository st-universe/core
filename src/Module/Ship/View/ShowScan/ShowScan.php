<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowScan;

use request;
use Stu\Component\Ship\ShipRumpEnum;
use Stu\Module\Ship\Lib\InteractionCheckerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;

final class ShowScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SCAN';

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $shipArray = $this->shipLoader->getWrappersByIdAndUserAndTarget(
            $shipId,
            $user->getId(),
            $targetId
        );

        $wrapper = $shipArray[$shipId];
        $ship = $wrapper->get();

        $targetWrapper = $shipArray[$targetId];
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();
        $game->setPageTitle(_('Scan'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_scan');
        if (!$this->interactionChecker->checkPosition($ship, $target)) {
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

        $game->setTemplateVar('TARGETWRAPPER', $targetWrapper);
        $game->setTemplateVar('SHIELD_PERCENTAGE', $this->calculateShieldPercentage($target));
        $game->setTemplateVar('REACTOR_PERCENTAGE', $this->calculateReactorPercentage($target));
        $game->setTemplateVar('SHIP', $ship);
        if (
            $target->getRump()->getRoleId() === ShipRumpEnum::SHIP_ROLE_ADVENT_DOOR
            && $ship->getMaxStorage() > $ship->getStorageSum()
        ) {
            $game->setTemplateVar('ADVENT_DAY', date("d.m.y"));
        }

        $tradePostCrewCount = null;
        $targetTradePost = $target->getTradePost();

        if ($targetTradePost !== null) {
            $tradePostCrewCount = $targetTradePost->getCrewCountOfUser($user);
        }
        $game->setTemplateVar('TRADE_POST_CREW_COUNT', $tradePostCrewCount);
    }

    private function calculateShieldPercentage(ShipInterface $target): int
    {
        return $target->getMaxShield() === 0
            ? 0
            : (int)ceil($target->getShield() / $target->getMaxShield() * 100);
    }

    private function calculateReactorPercentage(ShipInterface $target): int
    {
        return $target->getReactorCapacity() === 0
            ? 0
            : (int)ceil($target->getReactorLoad() / $target->getReactorCapacity() * 100);
    }
}
