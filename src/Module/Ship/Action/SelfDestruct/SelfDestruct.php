<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\SelfDestruct;

use request;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\History\Lib\EntryCreatorInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class SelfDestruct implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_SELFDESTRUCT';

    private $shipLoader;

    private $entryCreator;

    private $shipRemover;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        EntryCreatorInterface $entryCreator,
        ShipRemoverInterface $shipRemover
    ) {
        $this->shipLoader = $shipLoader;
        $this->entryCreator = $entryCreator;
        $this->shipRemover = $shipRemover;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        $code = request::postString('destructioncode');

        $game->addInformation('Das Selbstzerstörungssystem ist außer Betrieb.');
        // @todo repair
        return;

//        $this->entryCreator->addShipEntry(
//            sprintf(_('Die %s hat sich in Sektor %s selbst zerstört', $ship->getName(), $ship->getSectorString())),
//            $userId
//        );
//        $this->shipRemover->destroy($ship);
//        $game->redirectTo('ship.php?B_SELFDESTRUCT=1&sstr=' . $this->getSessionString());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
