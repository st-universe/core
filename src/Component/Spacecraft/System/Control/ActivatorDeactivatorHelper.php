<?php

declare(strict_types=1);

namespace Stu\Component\Spacecraft\System\Control;

use RuntimeException;
use Stu\Component\Spacecraft\SpacecraftLssModeEnum;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\Lib\Movement\Component\PreFlight\ConditionCheckResult;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;

final class ActivatorDeactivatorHelper implements ActivatorDeactivatorHelperInterface
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
    public function activate(
        SpacecraftWrapperInterface|int $target,
        SpacecraftSystemTypeEnum $type,
        ConditionCheckResult|InformationInterface $logger,
        bool $allowUplink = false,
        bool $isDryRun = false
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            $allowUplink,
            $this->spacecraftLoader,
            $this->game
        );

        return $this->systemActivation->activateIntern($wrapper, $type, $logger, $isDryRun);
    }

    #[\Override]
    public function activateFleet(
        int $shipId,
        SpacecraftSystemTypeEnum $type,
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
            if ($this->systemActivation->activateIntern($wrapper, $type, $game->getInfo(), false)) {
                $success = true;
            }
        }

        // only show info if at least one ship was able to change
        if (!$success) {
            return;
        }

        $game->getInfo()->addInformation(sprintf(
            _('Flottenbefehl ausgeführt: System %s aktiviert'),
            $type->getDescription()
        ));
    }

    #[\Override]
    public function deactivate(
        SpacecraftWrapperInterface|int $target,
        SpacecraftSystemTypeEnum $type,
        InformationInterface $informations,
        bool $allowUplink = false
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            $allowUplink,
            $this->spacecraftLoader,
            $this->game
        );

        return $this->systemDeactivation->deactivateIntern($wrapper, $type, $informations);
    }

    #[\Override]
    public function deactivateFleet(
        SpacecraftWrapperInterface|int $target,
        SpacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $wrapper = $this->getTargetWrapper(
            $target,
            false,
            $this->spacecraftLoader,
            $this->game
        );

        if (!$wrapper instanceof ShipWrapperInterface) {
            throw new RuntimeException('not a ship!');
        }

        return $this->deactivateFleetIntern($wrapper, $type, $informations);
    }

    private function deactivateFleetIntern(
        ShipWrapperInterface $wrapper,
        SpacecraftSystemTypeEnum $type,
        InformationInterface $informations
    ): bool {
        $fleetWrapper = $wrapper->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('ship not in fleet');
        }

        $success = false;
        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {
            if ($this->systemDeactivation->deactivateIntern($wrapper, $type, $informations)) {
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

    #[\Override]
    public function setLssMode(
        int $shipId,
        SpacecraftLssModeEnum $lssMode,
        GameControllerInterface $game
    ): void {
        $lss = $this->getTargetWrapper(
            $shipId,
            true,
            $this->spacecraftLoader,
            $this->game
        )->getLssSystemData();
        if ($lss === null) {
            throw new RuntimeException('this should not happen!');
        }

        $lss->setMode($lssMode)->update();

        if ($lssMode->isBorderMode()) {
            $game->getInfo()->addInformationf(
                '%s für die Langstreckensensoren aktiviert',
                $lssMode->getDescription()
            );
        } else {
            $game->getInfo()->addInformation('Filter für Langstreckensensoren wurde deaktiviert');
        }
    }
}
