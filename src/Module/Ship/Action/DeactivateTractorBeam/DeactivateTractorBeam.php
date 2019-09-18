<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateTractorBeam;

use request;
use Stu\Module\Communication\Lib\PrivateMessageSender;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_TRAKTOR';

    private $shipLoader;

    public function __construct(
        ShipLoaderInterface $shipLoader
    ) {
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$ship->isTraktorBeamActive()) {
            return;
        }
        if ($ship->getTraktorMode() == 2) {
            return;
        }
        if ($userId != $ship->getTraktorShip()->getUserId()) {
            PrivateMessageSender::sendPM($userId, $ship->getTraktorShip()->getUserId(),
                "Der auf die " . $ship->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in SeKtor " . $ship->getSectorString() . " deaktiviert",
                PM_SPECIAL_SHIP);
        }
        $ship->deactivateTraktorBeam();
        $game->addInformation("Der Traktorstrahl wurde deaktiviert");
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
