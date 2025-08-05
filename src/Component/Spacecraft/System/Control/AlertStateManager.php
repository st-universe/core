<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Control;

use BadMethodCallException;
use Override;
use Stu\Component\Spacecraft\SpacecraftAlertStateEnum;
use Stu\Component\Spacecraft\System\Exception\InsufficientEnergyException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Orm\Repository\SpacecraftRepositoryInterface;

final class AlertStateManager implements AlertStateManagerInterface
{
    use GetTargetWrapperTrait;

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader*/
    public function __construct(
        private readonly SpacecraftLoaderInterface $spacecraftLoader,
        private readonly SpacecraftRepositoryInterface $spacecraftRepository,
        private readonly SystemActivation $systemActivation,
        private readonly SystemDeactivation $systemDeactivation,
        private readonly GameControllerInterface $game
    ) {}

    #[Override]
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

        if (!$this->setAlertStateShip($wrapper, $alertState)) {
            return;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $this->game->getInfo()->addInformation("Die Alarmstufe wurde auf [b][color=red]Rot[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $this->game->getInfo()->addInformation("Die Alarmstufe wurde auf [b][color=yellow]Gelb[/color][/b] geändert");
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $this->game->getInfo()->addInformation("Die Alarmstufe wurde auf [b][color=green]Grün[/color][/b] geändert");
        }
    }

    #[Override]
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
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            $success = $this->setAlertStateShip($wrapper, $alertState) || $success;
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED) {
            $this->game->getInfo()->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=red]Rot[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_YELLOW) {
            $this->game->getInfo()->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=yellow]Gelb[/color][/b]'));
        } elseif ($alertState === SpacecraftAlertStateEnum::ALERT_GREEN) {
            $this->game->getInfo()->addInformation(_('Flottenbefehl ausgeführt: Alarmstufe [b][color=green]Grün[/color][/b]'));
        }
    }

    private function setAlertStateShip(SpacecraftWrapperInterface $wrapper, SpacecraftAlertStateEnum $alertState): bool
    {
        $ship = $wrapper->get();

        // station constructions can't change alert state
        if ($ship->isConstruction()) {
            $this->game->getInfo()->addInformationf('%s: [b][color=#ff2626]Konstrukte können die Alarmstufe nicht ändern[/color][/b]', $ship->getName());
            return false;
        }

        // can only change when there is enough crew
        if (!$ship->hasEnoughCrew()) {
            $this->game->getInfo()->addInformationf('%s: [b][color=#ff2626]Mangel an Crew verhindert den Wechsel der Alarmstufe[/color][/b]', $ship->getName());
            return false;
        }

        if ($alertState === SpacecraftAlertStateEnum::ALERT_RED && $ship->isCloaked()) {
            $this->game->getInfo()->addInformationf('%s: [b][color=#ff2626]Tarnung verhindert den Wechsel zu Alarm-Rot[/color][/b]', $ship->getName());
            return false;
        }

        try {
            $alertMsg = $wrapper->setAlertState($alertState);
            $this->spacecraftRepository->save($ship);

            if ($alertMsg !== null) {
                $this->game->getInfo()->addInformationf('%s: [b][color=FAFA03]%s[/color][/b]', $ship->getName(), $alertMsg);
            }
        } catch (InsufficientEnergyException $e) {
            $this->game->getInfo()->addInformationf('%s: [b][color=#ff2626]Nicht genügend Energie um die Alarmstufe zu wechseln (%d benötigt)[/color][/b]', $ship->getName(), $e->getNeededEnergy());
            return false;
        }

        match ($alertState) {
            SpacecraftAlertStateEnum::ALERT_RED =>
            $this->setAlertRed($wrapper),
            SpacecraftAlertStateEnum::ALERT_YELLOW =>
            $this->setAlertYellow($wrapper),
            SpacecraftAlertStateEnum::ALERT_GREEN =>
            $this->setAlertGreen($wrapper)
        };

        $this->spacecraftRepository->save($ship);

        return true;
    }

    private function setAlertRed(SpacecraftWrapperInterface $wrapper): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::SHIELDS,
            SpacecraftSystemTypeEnum::NBS,
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO
        ];

        foreach ($alertSystems as $type) {
            $this->systemActivation->activateIntern($wrapper, $type, $this->game->getInfo(), false);
        }
    }

    private function setAlertYellow(SpacecraftWrapperInterface $wrapper): void
    {
        $alertSystems = [
            SpacecraftSystemTypeEnum::NBS
        ];

        foreach ($alertSystems as $type) {
            $this->systemActivation->activateIntern($wrapper, $type, $this->game->getInfo(), false);
        }
    }

    private function setAlertGreen(SpacecraftWrapperInterface $wrapper): void
    {
        $deactivateSystems = [
            SpacecraftSystemTypeEnum::PHASER,
            SpacecraftSystemTypeEnum::TORPEDO,
            SpacecraftSystemTypeEnum::SHIELDS
        ];

        foreach ($deactivateSystems as $type) {
            if ($wrapper->get()->hasSpacecraftSystem($type)) {
                $this->systemDeactivation->deactivateIntern($wrapper, $type, $this->game->getInfo());
            }
        }
    }
}
