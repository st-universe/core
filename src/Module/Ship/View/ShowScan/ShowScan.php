<?php

declare(strict_types=1);

namespace Stu\Module\Ship\View\ShowScan;

use request;
use Stu\Lib\Pirate\PirateReactionInterface;
use Stu\Lib\Pirate\PirateReactionTriggerEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Control\ViewControllerInterface;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\PlayerSetting\Lib\UserEnum;
use Stu\Module\Ship\Lib\Interaction\InteractionCheckerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;

final class ShowScan implements ViewControllerInterface
{
    public const VIEW_IDENTIFIER = 'SHOW_SCAN';

    private ShipLoaderInterface $shipLoader;

    private InteractionCheckerInterface $interactionChecker;

    private PirateReactionInterface $pirateReaction;

    private PrivateMessageSenderInterface $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        InteractionCheckerInterface $interactionChecker,
        PirateReactionInterface $pirateReaction,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->interactionChecker = $interactionChecker;
        $this->pirateReaction = $pirateReaction;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $user = $game->getUser();

        $shipId = request::indInt('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $user->getId(),
            $targetId,
            true
        );

        $wrapper = $wrappers->getSource();
        $ship = $wrapper->get();

        $game->setMacroInAjaxWindow('html/entityNotAvailable.twig');

        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }

        $target = $targetWrapper->get();
        if ($target->getCloakState()) {
            return;
        }

        $game->setPageTitle(_('Scan'));
        $game->setMacroInAjaxWindow('html/shipmacros.xhtml/show_ship_scan');
        if (!$this->interactionChecker->checkPosition($ship, $target)) {
            $game->addInformation(_('Das Schiff befindet sich nicht in diesem Sektor'));
            return;
        }

        $epsSystem = $wrapper->getEpsSystemData();
        if ($epsSystem === null || $epsSystem->getEps() < 1) {
            $game->addInformation("Nicht genügend Energie vorhanden (1 benötigt)");
            return;
        }

        $epsSystem->lowerEps(1)->update();

        if ($target->getDatabaseId() !== 0) {
            $game->checkDatabaseItem($target->getDatabaseId());
        }
        if ($target->getRump()->getDatabaseId()) {
            $game->checkDatabaseItem($target->getRump()->getDatabaseId());
        }

        $href = sprintf('ship.php?%s=1&id=%d', ShowShip::VIEW_IDENTIFIER, $target->getId());

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
            $target->isBase() ? PrivateMessageFolderSpecialEnum::PM_SPECIAL_STATION : PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP,
            $href
        );

        $game->setTemplateVar('TARGETWRAPPER', $targetWrapper);
        $game->setTemplateVar('SHIELD_PERCENTAGE', $this->calculateShieldPercentage($target));
        $game->setTemplateVar('REACTOR_PERCENTAGE', $this->calculateReactorPercentage($targetWrapper));
        $game->setTemplateVar('SHIP', $ship);

        $tradePostCrewCount = null;
        $targetTradePost = $target->getTradePost();

        if ($targetTradePost !== null) {
            $tradePostCrewCount = $targetTradePost->getCrewCountOfUser($user);
        }
        $game->setTemplateVar('TRADE_POST_CREW_COUNT', $tradePostCrewCount);

        $targetFleet = $target->getFleet();
        if (
            $targetFleet !== null
            && $targetFleet->getUser()->getId() === UserEnum::USER_NPC_KAZON
        ) {
            $this->pirateReaction->react(
                $targetFleet,
                PirateReactionTriggerEnum::ON_SCAN,
                $ship
            );
        }
    }

    private function calculateShieldPercentage(ShipInterface $target): int
    {
        return $target->getMaxShield() === 0
            ? 0
            : (int)ceil($target->getShield() / $target->getMaxShield() * 100);
    }

    private function calculateReactorPercentage(ShipWrapperInterface $wrapper): ?int
    {
        $reactor = $wrapper->getReactorWrapper();
        if ($reactor === null) {
            return null;
        }

        return $reactor->getCapacity() === 0
            ? 0
            : (int)ceil($reactor->getLoad() / $reactor->getCapacity() * 100);
    }
}
