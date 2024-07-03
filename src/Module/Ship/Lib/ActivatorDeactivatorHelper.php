<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Lib;

use Override;
use RuntimeException;
use Stu\Component\Ship\ShipAlertStateEnum;
use Stu\Component\Ship\ShipLSSModeEnum;
use Stu\Component\Ship\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\AlreadyActiveException;
use Stu\Component\Ship\System\Exception\AlreadyOffException;
use Stu\Component\Ship\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Ship\System\Exception\InsufficientCrewException;
use Stu\Component\Ship\System\Exception\InsufficientEnergyException;
use Stu\Component\Ship\System\Exception\ShipSystemException;
use Stu\Component\Ship\System\Exception\SystemCooldownException;
use Stu\Component\Ship\System\Exception\SystemDamagedException;
use Stu\Component\Ship\System\Exception\SystemNotActivatableException;
use Stu\Component\Ship\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Ship\System\Exception\SystemNotFoundException;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Tal\TalHelper;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivatorDeactivatorHelper implements ActivatorDeactivatorHelperInterface
{
    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private ShipSystemManagerInterface $shipSystemManager, private GameControllerInterface $game)
    {
    }

    #[Override]
    public function activate(
        ShipWrapperInterface|int $target,
        shipSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $allowUplink = false,
        bool $isDryRun = false
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            $allowUplink
        );

        return $this->activateIntern($wrapper, $type, $logger, $isDryRun);
    }

    private function getTargetWrapper(
        ShipWrapperInterface|int $target,
        bool $allowUplink
    ): ShipWrapperInterface {
        if ($target instanceof ShipWrapperInterface) {
            return $target;
        }

        return $this->shipLoader->getWrapperByIdAndUser(
            $target,
            $this->game->getUser()->getId(),
            $allowUplink
        );
    }

    private function activateIntern(
        ShipWrapperInterface $wrapper,
        shipSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $isDryRun
    ): bool {
        $systemName = $type->getDescription();
        $ship = $wrapper->get();

        try {
            $this->shipSystemManager->activate($wrapper, $type, false, $isDryRun);
            $this->shipRepository->save($ship);
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s aktiviert'), $ship->getName(), $systemName);
            }
            return true;
        } catch (AlreadyActiveException) {
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s ist bereits aktiviert'), $ship->getName(), $systemName);
            }
        } catch (SystemNotActivatableException) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s besitzt keinen Aktivierungsmodus[/color][/b]'), $ship->getName(), $systemName), $logger);
        } catch (InsufficientEnergyException $e) {
            $this->logError($ship, sprintf(
                _('%s: [b][color=#ff2626]System %s kann aufgrund Energiemangels (%d benötigt) nicht aktiviert werden[/color][/b]'),
                $ship->getName(),
                $systemName,
                $e->getNeededEnergy()
            ), $logger);
        } catch (SystemCooldownException $e) {
            $this->logError($ship, sprintf(
                _('%s: [b][color=#ff2626]System %s kann nicht aktiviert werden, Cooldown noch %s[/color][/b]'),
                $ship->getName(),
                $systemName,
                TalHelper::formatSeconds((string) $e->getRemainingSeconds())
            ), $logger);
        } catch (SystemDamagedException) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s ist beschädigt und kann daher nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName), $logger);
        } catch (ActivationConditionsNotMetException $e) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden, weil %s[/color][/b]'), $ship->getName(), $systemName, $e->getMessage()), $logger);
        } catch (SystemNotFoundException) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s nicht vorhanden[/color][/b]'), $ship->getName(), $systemName), $logger);
        } catch (InsufficientCrewException) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s konnte wegen Mangel an Crew nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName), $logger);
        } catch (ShipSystemException) {
            $this->logError($ship, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden[/color][/b]'), $ship->getName(), $systemName), $logger);
        }

        return false;
    }

    private function logError(ShipInterface $ship, string $message, ConditionCheckResult|InformationInterface $logger): void
    {
        if ($logger instanceof InformationInterface) {
            $logger->addInformation($message);
        } else {
            $logger->addBlockedShip($ship, $message);
        }
    }

    #[Override]
    public function activateFleet(
        int $shipId,
        shipSystemTypeEnum $type,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('ship not in fleet');
        }

        $success = false;
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            if ($this->activateIntern($wrapper, $type, $game, false)) {
                $success = true;
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        $game->addInformation(sprintf(
            _('Flottenbefehl ausgeführt: System %s aktiviert'),
            $type->getDescription()
        ));
    }

    #[Override]
    public function deactivate(
        ShipWrapperInterface|int $target,
        shipSystemTypeEnum $type,
        InformationInterface $informations,
        bool $allowUplink = false
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            $allowUplink
        );

        return $this->deactivateIntern($wrapper, $type, $informations);
    }

    private function deactivateIntern(
        ShipWrapperInterface $wrapper,
        shipSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $systemName = $type->getDescription();
        $ship = $wrapper->get();

        try {
            $this->shipSystemManager->deactivate($wrapper, $type);
            $this->shipRepository->save($ship);
            $informations->addInformationf(_('%s: System %s deaktiviert'), $ship->getName(), $systemName);
            return true;
        } catch (AlreadyOffException) {
            $informations->addInformationf(_('%s: System %s ist bereits deaktiviert'), $ship->getName(), $systemName);
        } catch (SystemNotDeactivatableException) {
            $informations->addInformationf(_('%s: [b][color=#ff2626]System %s besitzt keinen Deaktivierungsmodus[/color][/b]'), $ship->getName(), $systemName);
        } catch (DeactivationConditionsNotMetException $e) {
            $informations->addInformationf(_('%s: [b][color=#ff2626]System %s konnte nicht deaktiviert werden, weil %s[/color][/b]'), $ship->getName(), $systemName, $e->getMessage());
        } catch (SystemNotFoundException) {
            $informations->addInformationf(_('%s: System %s nicht vorhanden'), $ship->getName(), $systemName);
        }

        return false;
    }

    #[Override]
    public function deactivateFleet(
        ShipWrapperInterface|int $target,
        shipSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            false
        );

        return $this->deactivateFleetIntern($wrapper, $type, $informations);
    }

    private function deactivateFleetIntern(
        ShipWrapperInterface $wrapper,
        shipSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('ship not in fleet');
        }

        $success = false;
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            if ($this->deactivateIntern($wrapper, $type, $informations)) {
                $success = true;
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return false;
        }

        $informations->addInformationf(
            'Flottenbefehl ausgeführt: System %s deaktiviert',
            $type->getDescription()
        );

        return true;
    }

    #[Override]
    public function setLSSMode(
        int $shipId,
        int $lssMode,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        $ship->setLSSMode($lssMode);
        $this->shipRepository->save($ship);

        if ($lssMode === ShipLSSModeEnum::LSS_NORMAL) {
            $game->addInformation("Territoriale Grenzanzeige deaktiviert");
        } elseif ($lssMode === ShipLSSModeEnum::LSS_BORDER) {
            $game->addInformation("Territoriale Grenzanzeige aktiviert");
        }
    }

    #[Override]
    public function setAlertState(
        ShipWrapperInterface|int $shipId,
        ShipAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void {
        $wrapper = $this->getTargetWrapper(
            $shipId,
            false
        );

        if (!$this->setAlertStateShip($wrapper, $alertState, $game)) {
            return;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }

    #[Override]
    public function setAlertStateFleet(
        int $shipId,
        ShipAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('ship not in fleet');
        }

        $success = false;
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $success = $this->setAlertStateShip($wrapper, $alertState, $game) || $success;
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } elseif ($alertState === ShipAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } elseif ($alertState === ShipAlertStateEnum::ALERT_GREEN) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        }
    }

    private function setAlertStateShip(ShipWrapperInterface $wrapper, ShipAlertStateEnum $alertState, GameControllerInterface $game): bool
    {
        $ship = $wrapper->get();

        // station constructions can't change alert state
        if ($ship->isConstruction()) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Konstrukte können die Alarmstufe nicht ändern[/color][/b]'), $ship->getName()));
            return false;
        }

        // can only change when there is enough crew
        if (!$ship->hasEnoughCrew()) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Mangel an Crew verhindert den Wechsel der Alarmstufe[/color][/b]'), $ship->getName()));
            return false;
        }

        if ($alertState === ShipAlertStateEnum::ALERT_RED && $ship->getCloakState()) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Tarnung verhindert den Wechsel zu Alarm-Rot[/color][/b]'), $ship->getName()));
            return false;
        }

        try {
            $alertMsg = $wrapper->setAlertState($alertState);
            $this->shipRepository->save($ship);

            if ($alertMsg !== null) {
                $game->addInformation(sprintf(_('%s: [b][color=FAFA03]%s[/color][/b]'), $ship->getName(), $alertMsg));
            }
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Nicht genügend Energie um die Alarmstufe zu wechseln (%d benötigt)[/color][/b]'), $ship->getName(), $e->getNeededEnergy()));
            return false;
        }

        switch ($alertState) {
            case ShipAlertStateEnum::ALERT_RED:
                $this->setAlertRed($wrapper, $game);
                break;
            case ShipAlertStateEnum::ALERT_YELLOW:
                $this->setAlertYellow($wrapper, $game);
                break;
            case ShipAlertStateEnum::ALERT_GREEN:
                $this->setAlertGreen($wrapper, $game);
                break;
        }

        $this->shipRepository->save($ship);

        return true;
    }

    private function setAlertRed(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            ShipSystemTypeEnum::SYSTEM_SHIELDS,
            ShipSystemTypeEnum::SYSTEM_NBS,
            ShipSystemTypeEnum::SYSTEM_PHASER,
            ShipSystemTypeEnum::SYSTEM_TORPEDO
        ];

        foreach ($alertSystems as $type) {
            $this->activateIntern($wrapper, $type, $game, false);
        }
    }

    private function setAlertYellow(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            ShipSystemTypeEnum::SYSTEM_NBS
        ];

        foreach ($alertSystems as $type) {
            $this->activateIntern($wrapper, $type, $game, false);
        }
    }

    private function setAlertGreen(ShipWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $deactivateSystems = [
            ShipSystemTypeEnum::SYSTEM_PHASER,
            ShipSystemTypeEnum::SYSTEM_TORPEDO,
            ShipSystemTypeEnum::SYSTEM_SHIELDS
        ];

        foreach ($deactivateSystems as $type) {
            if ($wrapper->get()->hasShipSystem($type)) {
                $this->deactivateIntern($wrapper, $type, $game);
            }
        }
    }
}
