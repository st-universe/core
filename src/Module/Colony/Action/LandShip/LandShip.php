<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LandShip;

use request;
use Ship;
use Stu\Module\Colony\Lib\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class LandShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_LAND_SHIP';

    private $colonyLoader;

    private $colonyStorageManager;

    private $colonyRepository;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        /**
         * @var Ship $ship
         */
        $ship = ResourceCache()->getObject('ship', request::getIntFatal('shipid'));
        if (!$ship->ownedByCurrentUser() || !$ship->canLandOnCurrentColony()) {
            return;
        }
        if (!$colony->storagePlaceLeft()) {
            $game->addInformation(_('Kein Lagerraum verfügbar'));
            return;
        }

        $this->colonyStorageManager->upperStorage($colony, $ship->getRump()->getCommodity(), 1);

        foreach ($ship->getStorage() as $stor) {
            $count = (int) min($stor->getAmount(), $colony->getMaxStorage() - $colony->getStorageSum());
            if ($count > 0) {
                $this->colonyStorageManager->upperStorage($colony, $stor->getCommodity(), $count);
            }
        }

        $this->colonyRepository->save($colony);

        $game->addInformationf(_('Die %s ist gelandet'), $ship->getName());
        $ship->remove();
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
