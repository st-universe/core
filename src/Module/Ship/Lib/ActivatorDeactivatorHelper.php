<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotActivableException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivableException;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Entity\ShipInterface;
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

    public function activate( int $shipId,
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

        $this->activateIntern($ship, $systemId, $systemName, $game);
        $this->shipRepository->save($ship);
    }

    private function activateIntern( ShipInterface $ship,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void
    {
        try {
            $this->shipSystemManager->activate($ship, $systemId);
            $game->addInformation(sprintf(_('%s: System %s aktiviert'), $ship->getName(), $systemName));
        } catch (AlreadyActiveException $e) {
            $game->addInformation(sprintf(_('%s: System %s ist bereits aktiviert'), $ship->getName(), $systemName));
        } catch (SystemNotActivableException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s besitzt keinen Aktivierungsmodus[/color][/b]'), $ship->getName(), $systemName));
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s kann aufgrund Energiemangels nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        } catch (SystemDamagedException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s ist beschädigt und kann daher nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        } catch (ShipSystemException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s konnte nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName));
        }
    }

    public function activateFleet( int $shipId,
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
            $this->activateIntern($ship, $systemId, $systemName, $game);
            $this->shipRepository->save($ship);
        }
        
        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: System %s aktiviert'), $systemName));
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

        $this->deactivateIntern($ship, $systemId, $systemName, $game);
        $this->shipRepository->save($ship);
    }

    private function deactivateIntern( ShipInterface $ship,
                                int $systemId,
                                string $systemName,
                                GameControllerInterface $game
                                ): void
    {
        try {
            $this->shipSystemManager->deactivate($ship, $systemId);
            $game->addInformation(sprintf(_('%s: System %s deaktiviert'), $ship->getName(), $systemName));
        } catch (AlreadyOffException $e) {
            $game->addInformation(sprintf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName));
        } catch (SystemNotDeactivableException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]System %s besitzt keinen Deaktivierungsmodus[/color][/b]'), $ship->getName(), $systemName));
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
            $this->deactivateIntern($ship, $systemId, $systemName, $game);
            $this->shipRepository->save($ship);
        }
        
        $game->addInformation(sprintf(_('Flottenbefehl ausgeführt: System %s deaktiviert'), $systemName));
    }

    public function setAlertState( int $shipId,
                                    int $alertState,
                                    GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        $this->setAlertStateShip($ship, $alertState, $game);

        if ($alertState === ShipAlertStateEnum::ALERT_RED)
        {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } 
        elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW)
        {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } 
        elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN)
        {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }
    
    public function setAlertStateFleet( int $shipId,
                                        int $alertState,
                                        GameControllerInterface $game): void
    {
        $userId = $game->getUser()->getId();
        
        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );
        
        foreach ($ship->getFleet()->getShips() as $ship) {
            $this->setAlertStateShip($ship, $alertState, $game);
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED)
        {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } 
        elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW)
        {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } 
        elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN)
        {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        } 
    }

    private function setAlertStateShip(ShipInterface $ship, int $alertState, GameControllerInterface $game): void
    {
        if ($alertState === ShipAlertStateEnum::ALERT_RED && $ship->getCloakState)
        {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Tarnung verhindert Wechsel zu Alarm-Rot[/color][/b]'), $ship->getName()));
            return;
        }

        try {
            $ship->setAlertState($alertState);
            $this->shipRepository->save($ship);
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=FF2626]Nicht genügend Energie um die Alarmstufe zu wechseln[/color][/b]'), $ship->getName()));
            return;
        }

        switch ($alertState) {
            case ShipAlertStateEnum::ALERT_RED:
                $this->setAlertRed($ship, $game);
                break;
            case ShipAlertStateEnum::ALERT_YELLOW:
                $this->setAlertYellow($ship, $game);
                break;
            case ShipAlertStateEnum::ALERT_GREEN:
                $this->setAlertGreen($ship, $game);
                break;
        }

        $this->shipRepository->save($ship);
    }

    private function setAlertRed(ShipInterface $ship, GameControllerInterface $game): void
    {
        $alertSystems = [
            'Schilde' => ShipSystemTypeEnum::SYSTEM_SHIELDS,
            'Nahbereichssensoren' => ShipSystemTypeEnum::SYSTEM_NBS,
            'Phaser' => ShipSystemTypeEnum::SYSTEM_PHASER,
            'Torpedowerfer' => ShipSystemTypeEnum::SYSTEM_TORPEDO
        ];

        foreach ($alertSystems as $key => $systemId)
        {
            $this->activateIntern($ship, $systemId, $key, $game);
        }
    }
    
    private function setAlertYellow(ShipInterface $ship, GameControllerInterface $game): void
    {
        $alertSystems = [
            'Nahbereichssensoren' => ShipSystemTypeEnum::SYSTEM_NBS
        ];
        
        foreach ($alertSystems as $key => $systemId)
        {
            $this->activateIntern($ship, $systemId, $key, $game);
        }
    }
    
    private function setAlertGreen(ShipInterface $ship, GameControllerInterface $game): void
    {
        $deactivateSystems = [
            'Phaser' => ShipSystemTypeEnum::SYSTEM_PHASER,
            'Torpedowerfer' => ShipSystemTypeEnum::SYSTEM_TORPEDO,
            'Schilde' => ShipSystemTypeEnum::SYSTEM_SHIELDS
        ];
        
        foreach ($deactivateSystems as $key => $systemId)
        {
            $this->deactivateIntern($ship, $systemId, $key, $game);
        }
    }
}
