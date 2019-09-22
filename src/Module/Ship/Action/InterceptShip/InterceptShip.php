<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\InterceptShip;

use request;
use Stu\Module\Communication\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class InterceptShip implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_INTERCEPT';

    private $shipLoader;

    private $privateMessageSender;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );
        $target = $this->shipLoader->getById(request::indInt('target'));
        if (!checkPosition($target, $ship)) {
            return;
        }

        if ($ship->getBuildplan()->getCrew() > 0 && $ship->getCrew() == 0) {
            $game->addInformationf(
                _("Es werden %d Crewmitglieder benötigt"),
                $ship->getBuildplan()->getCrew()
            );
            return;
        }

        if (!$target->getWarpState()) {
            return;
        }
        if ($target->ownedByCurrentUser()) {
            return;
        }
        if (!$ship->canIntercept()) {
            return;
        }
        if ($ship->getDock()) {
            $game->addInformation('Das Schiff hat abgedockt');
            $ship->setDock(0);
        }
        if ($target->isInFleet()) {
            $target->getFleet()->deactivateSystem(SYSTEM_WARPDRIVE);
            $game->addInformation("Die Flotte " . $target->getFleet()->getName() . " wurde abgefangen");
            $pm = "Die Flotte " . $target->getFleet()->getName() . " wurde von der " . $ship->getName() . " abgefangen";
        } else {
            $target->deactivateSystem(SYSTEM_WARPDRIVE);
            $game->addInformation("Die " . $target->getName() . "  wurde abgefangen");
            $pm = "Die " . $target->getName() . " wurde von der " . $ship->getName() . " abgefangen";
            $target->save();
        }

        $this->privateMessageSender->send($userId, (int)$target->getUserId(), $pm, PM_SPECIAL_SHIP);
        if ($ship->isInFleet()) {
            $ship->getFleet()->deactivateSystem(SYSTEM_WARPDRIVE);
        } else {
            $ship->deactivateSystem(SYSTEM_WARPDRIVE);
            $ship->save();
        }
        // @todo TBD Red alert
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
