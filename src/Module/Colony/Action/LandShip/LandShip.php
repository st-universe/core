<?php

declare(strict_types=1);

namespace Stu\Module\Colony\Action\LandShip;

use request;
use Stu\Component\Colony\Storage\ColonyStorageManagerInterface;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Colony\Lib\ColonyLoaderInterface;
use Stu\Module\Colony\View\ShowColony\ShowColony;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipRemoverInterface;
use Stu\Orm\Repository\ColonyRepositoryInterface;

final class LandShip implements ActionControllerInterface
{

    public const ACTION_IDENTIFIER = 'B_LAND_SHIP';

    private ColonyLoaderInterface $colonyLoader;

    private ColonyStorageManagerInterface $colonyStorageManager;

    private ColonyRepositoryInterface $colonyRepository;

    private ShipRemoverInterface $shipRemover;

    private ShipLoaderInterface $shipLoader;

    public function __construct(
        ColonyLoaderInterface $colonyLoader,
        ColonyStorageManagerInterface $colonyStorageManager,
        ColonyRepositoryInterface $colonyRepository,
        ShipRemoverInterface $shipRemover,
        ShipLoaderInterface $shipLoader
    ) {
        $this->colonyLoader = $colonyLoader;
        $this->colonyStorageManager = $colonyStorageManager;
        $this->colonyRepository = $colonyRepository;
        $this->shipRemover = $shipRemover;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $colony = $this->colonyLoader->byIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $game->setView(ShowColony::VIEW_IDENTIFIER);

        $ship = $this->shipLoader->find(request::getIntFatal('shipid'));
        if ($ship->getUser()->getId() !== $game->getUser()->getId() || !$ship->canLandOnCurrentColony()) {
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

        $this->shipRemover->remove($ship);

        $game->addInformationf(_('Die %s ist gelandet'), $ship->getName());
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
