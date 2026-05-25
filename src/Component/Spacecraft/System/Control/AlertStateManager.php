<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Control;

use BadMethodCallException;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Lib\Information\InformationWrapper;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class AlertStateManager implements AlertStateManagerInterface
{
    use GetTargetWrapperTrait;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    public function __construct(
        private readonly SpacecraftLoaderInterface $spacecraftLoader,
        private readonly SystemActivation $systemActivation,
        private readonly SystemDeactivation $systemDeactivation,
        private readonly GameControllerInterface $game
    ) {}

    #[\Override]
    public function setAlertState(
        SpacecraftWrapperInterface|int $target,
        SpacecraftAlertStateEnum $alertState
    ): void {
        $wrapper = $this->getTargetWrapper(
            $target,
            false,
            $this->spacecraftLoader,
            $this->game
        );

        $visibleInformation = $this->getVisibleInformation($wrapper);
        $information = $visibleInformation ?? new InformationWrapper();

        if (!$this->setAlertStateShip($wrapper, $alertState, $information)) {
            return;
        }

        if ($visibleInformation === null) {
            return;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $visibleInformation->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $visibleInformation->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $visibleInformation->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }

    #[\Override]
    public function setAlertStateFleet(
        int $shipId,
        SpacecraftAlertStateEnum $alertState
    ): void {
        $userId = $this->game->getUser()->getId();

        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            $shipId,
            $userId
        );

        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new BadMethodCallException('ship not in fleet');
        }

        $success = false;
        $information = $this->game->getInfo();
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $success = $this->setAlertStateShip($wrapper, $alertState, $information) || $success;
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $information->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $information->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $information->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        }
    }

    private function setAlertStateShip(
        SpacecraftWrapperInterface $wrapper,
        SpacecraftAlertStateEnum $alertState,
        InformationInterface $information
    ): bool
    {
        $ship = $wrapper->get();

        // station constructions can't change alert state
        if ($ship->isConstruction()) {
            $information->addInformationf('%s: [b][color=#ff2626]Konstrukte können die Alarmstufe nicht ändern[/color][/b]', $ship->getName());
            return false;
        }

        // can only change when there is enough crew
        if (!$ship->hasEnoughCrew()) {
            $information->addInformationf('%s: [b][color=#ff2626]Mangel an Crew verhindert den Wechsel der Alarmstufe[/color][/b]', $ship->getName());
            return false;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED && $ship->isCloaked()) {
            $information->addInformationf('%s: [b][color=#ff2626]Tarnung verhindert den Wechsel zu Alarm-Rot[/color][/b]', $ship->getName());
            return false;
        }

        try {
            $alertMsg = $wrapper->setAlertState($alertState);

            if ($alertMsg !== null) {
                $information->addInformationf('%s: [b][color=FAFA03]%s[/color][/b]', $ship->getName(), $alertMsg);
            }
        } catch (InsufficientEnergyException $e) {
            $information->addInformationf('%s: [b][color=#ff2626]Nicht genügend Energie um die Alarmstufe zu wechseln (%d benötigt)[/color][/b]', $ship->getName(), $e->getNeededEnergy());
            return false;
        }

        match ($alertState) {
            SpacecraftAlertStateEnum::ALERT_RED =>
            $this->setAlertRed($wrapper, $information),
            SpacecraftAlertStateEnum::ALERT_YELLOW =>
            $this->setAlertYellow($wrapper, $information),
            SpacecraftAlertStateEnum::ALERT_GREEN =>
            $this->setAlertGreen($wrapper, $information)
        };

        return true;
    }

    private function setAlertRed(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::SHIELDS,
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ];

        foreach ($alertSystems as $type) {
            $this->systemActivation->activateIntern($wrapper, $type, $information, false);
        }
    }

    private function setAlertYellow(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::NBS
        ];

        foreach ($alertSystems as $type) {
            $this->systemActivation->activateIntern($wrapper, $type, $information, false);
        }
    }

    private function setAlertGreen(SpacecraftWrapperInterface $wrapper, InformationInterface $information): void
    {
        $deactivateSystems = [
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO,
            SpacecraftSystemTypeEnum::SHIELDS
        ];

        foreach ($deactivateSystems as $type) {
            if ($wrapper->get()->hasSpacecraftSystem($type)) {
                $this->systemDeactivation->deactivateIntern($wrapper, $type, $information);
            }
        }
    }

    private function getVisibleInformation(SpacecraftWrapperInterface $wrapper): ?InformationInterface
    {
        if (!$this->game->hasUser()) {
            return null;
        }

        if ($wrapper->get()->getUserId() !== $this->game->getUser()->getId()) {
            return null;
        }

        return $this->game->getInfo();
    }
}
