<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateTractorBeam;

use request;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Message\Lib\PrivateMessageFolderSpecialEnum;
use Stu\Module\Message\Lib\PrivateMessageSenderInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;

final class DeactivateTractorBeam implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_TRAKTOR';

    private ShipLoaderInterface $shipLoader;

    private PrivateMessageSenderInterface $privateMessageSender;

    private ActivatorDeactivatorHelperInterface $helper;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        PrivateMessageSenderInterface $privateMessageSender,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->privateMessageSender = $privateMessageSender;
        $this->helper = $helper;
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
            $this->privateMessageSender->send(
                $userId,
                (int) $ship->getTraktorShip()->getUserId(),
                "Der auf die " . $ship->getTraktorShip()->getName() . " gerichtete Traktorstrahl wurde in Sektor " . $ship->getSectorString() . " deaktiviert",
                PrivateMessageFolderSpecialEnum::PM_SPECIAL_SHIP
            );
        }
        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
