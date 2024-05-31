<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\TrackShip;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Interaction\InteractionChecker;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class TrackShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_TRACK';

    private const MAXIMUM_TICKS = 70;

    private ShipLoaderInterface $shipLoader;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $shipId = request::getIntFatal('id');
        $targetId = request::getIntFatal('target');

        $wrappers = $this->shipLoader->getWrappersBySourceAndUserAndTarget(
            $shipId,
            $userId,
            $targetId
        );

        $wrapper = $wrappers->getSource();
        $targetWrapper = $wrappers->getTarget();
        if ($targetWrapper === null) {
            return;
        }
        $target = $targetWrapper->get();

        $ship = $wrapper->get();
        if (!$ship->hasEnoughCrew($game)) {
            return;
        }

        if ($target->getShieldState()) {
            $game->addInformation(_("Das Ziel hat die Schilde aktiviert"));
            return;
        }

        if (!InteractionChecker::canInteractWith($ship, $target, $game, true)) {
            return;
        }

        $tracker = $wrapper->getTrackerSystemData();

        if ($tracker === null || $tracker->targetId !== null) {
            return;
        }

        $eps = $wrapper->getEpsSystemData();
        if ($eps === null || $eps->getEps() === 0) {
            $game->addInformation(_("Keine Energie vorhanden"));
            return;
        }

        if ($ship->getCloakState()) {
            $game->addInformation(_("Die Tarnung ist aktiviert"));
            return;
        }
        if ($ship->isWarped()) {
            $game->addInformation("Schiff befindet sich im Warp");
            return;
        }

        if ($target->isDestroyed()) {
            return;
        }
        if ($target->isWarped()) {
            $game->addInformationf('Die %s befindet sich im Warp', $target->getName());
            return;
        }

        // activate system
        if (!$this->helper->activate($wrapper, ShipSystemTypeEnum::SYSTEM_TRACKER, $game)) {
            $game->setView(ShowShip::VIEW_IDENTIFIER);
            return;
        }

        $tracker->setTarget($target->getId())
            ->setRemainingTicks(self::MAXIMUM_TICKS)
            ->update();


        $game->addInformation(sprintf(_('Die %s ist nun mit einem verborgenen Tracker markiert'), $target->getName()));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
