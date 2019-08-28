<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SalvageEmergencyPods;

use PM;
use request;
use ShipCrew;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;

final class SalvageEmergencyPods implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SALVAGE_EPODS';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $target = ResourceCache()->getObject(CACHE_SHIP, request::postIntFatal('target'));
        $ship->canInteractWith($target);
        if ($target->getCrew() == 0) {
            $game->addInformation(_('Keine Rettungskapseln vorhanden'));
            return;
        }
        if ($ship->getEps() < 1) {
            $game->addInformation(sprintf(_('Zum Bergen der Rettungskapseln wird %d Energie benötigt'), 1));
            return;
        }
        $ship->cancelRepair();
        $dummy_crew = current($target->getCrewList());
        if ($dummy_crew->getCrew()->getUserId() != $userId) {
            PM::sendPm($userId, $dummy_crew->getCrew()->getUserId(),
                sprintf(_('Der Siedler hat %d deiner Crewmitglieder von einem Trümmerfeld geborgen.'),
                    $target->getCrew()), PM_SPECIAL_SHIP);
        }
        ShipCrew::truncate('WHERE ships_id=' . $target->getId());
        $ship->lowerEps(1);
        $ship->save();
        $game->addInformation(_('Die Rettungskapseln wurden geborgen'));
    }

    public function performSessionCheck(): bool
    {
        return false;
    }
}
