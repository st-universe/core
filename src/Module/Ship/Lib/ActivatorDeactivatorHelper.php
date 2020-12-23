<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivableException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivatorDeactivatorHelper implements ActivatorDeactivatorHelperInterface
{
    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    private ShipSystemManagerInterface $shipSystemManager;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ShipSystemManagerInterface $shipSystemManager
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->shipSystemManager = $shipSystemManager;
    }

    public function deactivate( int $shipId,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        try {
            $this->shipSystemManager->deactivate($ship, $systemId);
            $this->shipRepository->save($ship);
            $game->addInformation(_('%s: System %s deaktiviert'), $ship->getName(), $systemName));
        } catch (AlreadyOffException $e) {
            $game->addInformation(sprintf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName));
        } catch (SystemNotDeactivableException $e) {
            $game->addInformation(sprintf(_('%s: System %s kann nicht deaktiviert werden'), $ship->getName(), $systemName));
        }
    }

    public function deactivateFleet( int $shipId,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        foreach ($ship->getFleet()->getShips() as $ship) {
            try {
                $this->shipSystemManager->deactivate($ship, $systemId);
                $this->shipRepository->save($ship);
                $game->addInformation(_('%s: System %s deaktiviert'), $ship->getName(), $systemName));
            } catch (AlreadyOffException $e) {
                $game->addInformation(sprintf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName));
            } catch (SystemNotDeactivableException $e) {
                $game->addInformation(sprintf(_('%s: System %s kann nicht deaktiviert werden'), $ship->getName(), $systemName));
            }
        }
        
        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: System %s deaktiviert'), $systemName));
    }
}
