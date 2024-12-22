<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Lib;

use Override;
use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\System\Exception\ActivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\AlreadyActiveException;
use Stu\Component\Spacecraft\System\Exception\AlreadyOffException;
use Stu\Component\Spacecraft\System\Exception\DeactivationConditionsNotMetException;
use Stu\Component\Spacecraft\System\Exception\InsufficientCrewException;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\Exception\SpacecraftSystemException;
use Stu\Component\Spacecraft\System\Exception\SystemCooldownException;
use Stu\Component\Spacecraft\System\Exception\SystemDamagedException;
use Stu\Component\Spacecraft\System\Exception\SystemNotActivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotDeactivatableException;
use Stu\Component\Spacecraft\System\Exception\SystemNotFoundException;
use Stu\Component\Spacecraft\System\SpacecraftSystemManagerInterface;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Template\TemplateHelperInterface;
use Stu\Orm\Entity\ShipInterface;
use Stu\Orm\Entity\SpacecraftInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class ActivatorDeactivatorHelper implements ActivatorDeactivatorHelperInterface
{
    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    public function __construct(
        private SpacecraftLoaderInterface $spacecraftLoader,
        private SpacecraftRepositoryInterface $spacecraftRepository,
        private SpacecraftSystemManagerInterface $spacecraftSystemManager,
        private TemplateHelperInterface $templateHelper,
        private GameControllerInterface $game
    ) {}

    #[Override]
    public function activate(
        SpacecraftWrapperInterface|int $target,
        spacecraftSystemTypeEnum $type,
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
        SpacecraftWrapperInterface|int $target,
        bool $allowUplink
    ): SpacecraftWrapperInterface {
        if ($target instanceof SpacecraftWrapperInterface) {
            return $target;
        }

        return $this->spacecraftLoader->getWrapperByIdAndUser(
            $target,
            $this->game->getUser()->getId(),
            $allowUplink
        );
    }

    private function activateIntern(
        SpacecraftWrapperInterface $wrapper,
        spacecraftSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $isDryRun
    ): bool {
        $systemName = $type->getDescription();
        $spacecraft = $wrapper->get();

        try {
            $this->spacecraftSystemManager->activate($wrapper, $type, false, $isDryRun);
            $this->spacecraftRepository->save($spacecraft);
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s aktiviert'), $spacecraft->getName(), $systemName);
            }
            return true;
        } catch (AlreadyActiveException) {
            if ($logger instanceof InformationInterface) {
                $logger->addInformationf(_('%s: System %s ist bereits aktiviert'), $spacecraft->getName(), $systemName);
            }
        } catch (SystemNotActivatableException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s besitzt keinen Aktivierungsmodus[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (InsufficientEnergyException $e) {
            $this->logError($spacecraft, sprintf(
                _('%s: [b][color=#ff2626]System %s kann aufgrund Energiemangels (%d benötigt) nicht aktiviert werden[/color][/b]'),
                $spacecraft->getName(),
                $systemName,
                $e->getNeededEnergy()
            ), $logger);
        } catch (SystemCooldownException $e) {
            $this->logError($spacecraft, sprintf(
                _('%s: [b][color=#ff2626]System %s kann nicht aktiviert werden, Cooldown noch %s[/color][/b]'),
                $spacecraft->getName(),
                $systemName,
                $this->templateHelper->formatSeconds((string) $e->getRemainingSeconds())
            ), $logger);
        } catch (SystemDamagedException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s ist beschädigt und kann daher nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (ActivationConditionsNotMetException $e) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden, weil %s[/color][/b]'), $spacecraft->getName(), $systemName, $e->getMessage()), $logger);
        } catch (SystemNotFoundException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s nicht vorhanden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (InsufficientCrewException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte wegen Mangel an Crew nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        } catch (SpacecraftSystemException) {
            $this->logError($spacecraft, sprintf(_('%s: [b][color=#ff2626]System %s konnte nicht aktiviert werden[/color][/b]'), $spacecraft->getName(), $systemName), $logger);
        }

        return false;
    }

    private function logError(SpacecraftInterface $spacecraft, string $message, ConditionCheckResult|InformationInterface $logger): void
    {
        if ($logger instanceof InformationInterface) {
            $logger->addInformation($message);
        } elseif ($spacecraft instanceof ShipInterface) {
            $logger->addBlockedShip($spacecraft, $message);
        }
    }

    #[Override]
    public function activateFleet(
        int $shipId,
        spacecraftSystemTypeEnum $type,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
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
        SpacecraftWrapperInterface|int $target,
        spacecraftSystemTypeEnum $type,
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
        SpacecraftWrapperInterface $wrapper,
        spacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $systemName = $type->getDescription();
        $ship = $wrapper->get();

        try {
            $this->spacecraftSystemManager->deactivate($wrapper, $type);
            $this->spacecraftRepository->save($ship);
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
        spacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            false
        );

        if (!$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException('not a ship!');
        }

        return $this->deactivateFleetIntern($wrapper, $type, $informations);
    }

    private function deactivateFleetIntern(
        ShipWrapperInterface $wrapper,
        spacecraftSystemTypeEnum $type,
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
    public function setLssMode(
        int $shipId,
        SpacecraftLssModeEnum $lssMode,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $ship = $this->spacecraftLoader->getByIdAndUser(
            $shipId,
            $userId
        );

        $ship->setLssMode($lssMode);
        $this->spacecraftRepository->save($ship);

        if ($lssMode->isBorderMode()) {
            $game->addInformation("Territoriale Grenzanzeige aktiviert");
        } else {
            $game->addInformation("Territoriale Grenzanzeige deaktiviert");
        }
    }

    #[Override]
    public function setAlertState(
        SpacecraftWrapperInterface|int $target,
        SpacecraftAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void {
        $wrapper = $this->getTargetWrapper(
            $target,
            false
        );

        if (!$this->setAlertStateShip($wrapper, $alertState, $game)) {
            return;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $game->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }

    #[Override]
    public function setAlertStateFleet(
        int $shipId,
        SpacecraftAlertStateEnum $alertState,
        GameControllerInterface $game
    ): void {
        $userId = $game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
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

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $game->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        }
    }

    private function setAlertStateShip(SpacecraftWrapperInterface $wrapper, SpacecraftAlertStateEnum $alertState, GameControllerInterface $game): bool
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

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED && $ship->getCloakState()) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Tarnung verhindert den Wechsel zu Alarm-Rot[/color][/b]'), $ship->getName()));
            return false;
        }

        try {
            $alertMsg = $wrapper->setAlertState($alertState);
            $this->spacecraftRepository->save($ship);

            if ($alertMsg !== null) {
                $game->addInformation(sprintf(_('%s: [b][color=FAFA03]%s[/color][/b]'), $ship->getName(), $alertMsg));
            }
        } catch (InsufficientEnergyException $e) {
            $game->addInformation(sprintf(_('%s: [b][color=#ff2626]Nicht genügend Energie um die Alarmstufe zu wechseln (%d benötigt)[/color][/b]'), $ship->getName(), $e->getNeededEnergy()));
            return false;
        }

        switch ($alertState) {
            case SpacecraftAlertStateEnum::ALERT_RED:
                $this->setAlertRed($wrapper, $game);
                break;
            case SpacecraftAlertStateEnum::ALERT_YELLOW:
                $this->setAlertYellow($wrapper, $game);
                break;
            case SpacecraftAlertStateEnum::ALERT_GREEN:
                $this->setAlertGreen($wrapper, $game);
                break;
        }

        $this->spacecraftRepository->save($ship);

        return true;
    }

    private function setAlertRed(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::SHIELDS,
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ];

        foreach ($alertSystems as $type) {
            $this->activateIntern($wrapper, $type, $game, false);
        }
    }

    private function setAlertYellow(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::NBS
        ];

        foreach ($alertSystems as $type) {
            $this->activateIntern($wrapper, $type, $game, false);
        }
    }

    private function setAlertGreen(SpacecraftWrapperInterface $wrapper, GameControllerInterface $game): void
    {
        $deactivateSystems = [
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO,
            SpacecraftSystemTypeEnum::SHIELDS
        ];

        foreach ($deactivateSystems as $type) {
            if ($wrapper->get()->hasSpacecraftSystem($type)) {
                $this->deactivateIntern($wrapper, $type, $game);
            }
        }
    }
}
