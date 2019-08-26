<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LandShip;

use request;
use Stu\Control\ActionControllerInterface;
use Stu\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;

final class LandShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_LAND_SHIP';

    private $colonyLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader
    ) {
        $this->colonyLoader = $colonyLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        /**
         * @var \Ship $ship
         */
        $ship = ResourceCache()->getObject('ship', request::getIntFatal('shipid'));
        if (!$ship->ownedByCurrentUser() || !$ship->canLandOnCurrentColony()) {
            return;
        }
        if (!$colony->storagePlaceLeft()) {
            $game->addInformation(_('Kein Lagerraum verfügbar'));
            return;
        }
        $colony->upperStorage($ship->getRump()->getGoodId(), 1);
        $colony->setStorageSum($colony->getStorageSum() + 1);
        foreach ($ship->getStorage() as $key => $stor) {
            $stor->getAmount() + $colony->getStorageSum() > $colony->getMaxStorage() ? $count = $colony->getMaxStorage() - $colony->getStorageSum() : $count = $stor->getAmount();
            if ($count > 0) {
                $colony->upperStorage($stor->getGoodId(), $count);
                $colony->setStorageSum($colony->getStorageSum() + $count);
            }
        }
        $colony->save();
        $game->addInformationf(_('Die %s ist gelandet'), $ship->getName());
        $ship->remove();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
