<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetAlertGreen;

use request;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class FleetAlertGreen implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_FLEET_ALERT_GREEN';

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
            $ship->setAlertState(ShipAlertStateEnum::ALERT_GREEN);

            $deactivateSystems = [
                ShipSystemTypeEnum::SYSTEM_PHASER,
                ShipSystemTypeEnum::SYSTEM_TORPEDO,
                ShipSystemTypeEnum::SYSTEM_SHIELDS,
            ];
            
            foreach ($deactivateSystems as $systemId) {
                try {
                    $this->shipSystemManager->deactivate($ship, $systemId);
                } catch (ShipSystemException $e) {
                    continue;
                }
            }

            $this->shipRepository->save($ship);
        }
        $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe Grün'));
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
