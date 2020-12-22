<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetAlertRed;

use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetAlertRed implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ALERT_RED';

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

        foreach ($ship->getFleet()->getShips() as $key => $ship) {
            
            try {
                $ship->setAlertState(ShipAlertStateEnum::ALERT_RED);
            } catch (ShipSystemException $e) {
                $game->addInformation(sprintf(_('%s: Nicht genügend Energie um auf Alarm-Rot zu wechseln'), $ship->getName()));
                continue;
            }
            
            $alertSystems = [
                'Schilde' => ShipSystemTypeEnum::SYSTEM_SHIELDS,
                'Nahbereichssensoren' => ShipSystemTypeEnum::SYSTEM_NBS,
                'Phaser' => ShipSystemTypeEnum::SYSTEM_PHASER,
                'Torpedowerfer' => ShipSystemTypeEnum::SYSTEM_TORPEDO
            ];
            
            foreach ($alertSystems as $key => $systemId) {
                try {
                    $this->shipSystemManager->activate($ship, $systemId);
                    $game->addInformation(sprintf(_('%s: System %s wurde aktiviert'), $ship->getName(), $key));
                } catch (AlreadyActiveException $e) {
                    $game->addInformation(sprintf(_('%s: System %s ist bereits aktiviert'), $ship->getName(), $key));
                    continue;
                } catch (ShipSystemException $e) {
                    $game->addInformation(sprintf(_('%s: System %s konnte nicht aktiviert werden'), $ship->getName(), $key));
                    continue;
                }
            }

            $this->shipRepository->save($ship);
        }
        $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Rot'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
