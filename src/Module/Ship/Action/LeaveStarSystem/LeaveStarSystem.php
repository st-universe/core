<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\LeaveStarSystem;

use request;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class LeaveStarSystem implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_LEAVE_STARSYSTEM';

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

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->getSystem() === null) {
            return;
        }
        try {
            $this->shipSystemManager->activate(
                $ship,
                ShipSystemTypeEnum::SYSTEM_WARPDRIVE
            );

            $this->shipRepository->save($ship);
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(_('Nicht genügend Energie zur Aktivierung vorhanden'));
            return;
        } catch (SystemDamagedException $e) {
            $game->addInformation(_('Der Warpantrieb ist beschädigt und kann nicht aktiviert werden'));
            return;
        } catch (ShipSystemException $e) {
            $game->addInformation(_('Der Warpantrieb konnte nicht aktiviert werden'));
            return;
        }

        $this->leaveStarSystem($ship);
        if ($ship->isTraktorbeamActive()) {
            $this->leaveStarSystemTraktor($ship, $game);
        }
        if ($ship->isFleetLeader()) {
            $msg = array();

            /** @var ShipInterface[] $result */
            $result = array_filter(
                $ship->getFleet()->getShips()->toArray(),
                function (ShipInterface $fleetShip) use ($ship): bool {
                    return $ship !== $fleetShip;
                }
            );
            foreach ($result as $fleetShip) {
                try {
                    $this->shipSystemManager->activate($fleetShip, ShipSystemTypeEnum::SYSTEM_WARPDRIVE);
                } catch (ShipSystemException $e) {
                    $msg[] = "Die " . $ship->getName() . " hat die Flotte verlassen. Grund: Warpantrieb kann nicht aktiviert werden";
                    $fleetShip->leaveFleet();
                }

                $this->leaveStarSystem($fleetShip);
                if ($fleetShip->isTraktorbeamActive()) {
                    $this->leaveStarSystemTraktor($fleetShip, $game);
                }

                $this->shipRepository->save($fleetShip);
            }
            $game->addInformation("Die Flotte hat das Sternsystem verlassen");
            $game->addInformationMerge($msg);
        } else {
            if ($ship->getFleetId()) {
                $ship->leaveFleet();
                $game->addInformation("Das Schiff hat die Flotte verlassen");
            }
            $game->addInformation("Das Sternsystem wurde verlassen");
        }

        $this->shipRepository->save($ship);
    }

    private function leaveStarSystemTraktor(ShipInterface $ship, GameControllerInterface $game): void
    {
        if ($ship->getEps() < 1) {
            $ship->getTraktorShip()->unsetTraktor();

            $this->shipRepository->save($ship->getTraktorShip());

            $ship->unsetTraktor();
            $game->addInformation("Der Traktorstrahl auf die " . $ship->getTraktorShip()->getName() . " wurde beim Verlassen des Systems aufgrund Energiemangels deaktiviert");
            return;
        }
        $this->leaveStarSystem($ship->getTraktorShip());
        $ship->setEps($ship->getEps() - 1);

        $this->shipRepository->save($ship->getTraktorShip());
        $this->shipRepository->save($ship);

        $game->addInformation("Die " . $ship->getTraktorShip()->getName() . " wurde mit aus dem System gezogen");
    }

    private function leaveStarSystem(ShipInterface $ship): void {
        $ship->setSystem(null);
        $ship->setSX(0);
        $ship->setSY(0);
    }


    public function performSessionCheck(): bool
    {
        return true;
    }
}
