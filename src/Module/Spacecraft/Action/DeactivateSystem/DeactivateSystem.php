<?php

declare(strict_types=1);

namespace Stu\Module\Spacecraft\Action\DeactivateSystem;

use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Lib\Information\InformationInterface;
use Stu\Module\Ship\Lib\FleetWrapperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftLoaderInterface;
use Stu\Module\Spacecraft\Lib\SpacecraftWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

final class DeactivateSystem implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_SYSTEM';

    /** @param SpacecraftLoaderInterface<SpacecraftWrapperInterface> $spacecraftLoader */
    public function __construct(
        private ActivatorDeactivatorHelperInterface $helper,
        private SpacecraftLoaderInterface $spacecraftLoader,
        private AlertReactionFacadeInterface $alertReactionFacade
    ) {}

    #[\Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->spacecraftLoader->getWrapperByIdAndUser(
            request::getIntFatal('id'),
            $game->getUser()->getId()
        );

        $fleetWrapper = request::getInt('isfleet') ? $wrapper->getFleetWrapper() : null;
        $systemType = SpacecraftSystemTypeEnum::getByName(request::getStringFatal('type'));

        $tractoredShipWrapper = null;
        $tractoredShips = [];

        if ($this->isAlertReactionCheckNeeded($systemType)) {
            if ($fleetWrapper === null) {
                $tractoredShipWrapper = $wrapper->getTractoredShipWrapper();
            } else {
                $tractoredShips = $this->getTractoredShipWrappers($fleetWrapper);
            }
        }

        if ($fleetWrapper === null) {
            $success = $this->helper->deactivate(
                $wrapper,
                $systemType,
                $game->getInfo()
            );
        } else {
            $success = $this->helper->deactivateFleet(
                $wrapper,
                $systemType,
                $game->getInfo()
            );
        }

        if ($success && $this->isAlertReactionCheckNeeded($systemType)) {
            $this->triggerAlertReaction($wrapper, $tractoredShipWrapper, $tractoredShips, $game->getInfo());
            if ($wrapper->get()->getCondition()->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    private function isAlertReactionCheckNeeded(SpacecraftSystemTypeEnum $systemType): bool
    {
        return match ($systemType) {
            SpacecraftSystemTypeEnum::CLOAK,
            SpacecraftSystemTypeEnum::WARPDRIVE => true,
            default => false
        };
    }

    /** 
     * @param array<int, array{0: Ship, 1: SpacecraftWrapperInterface}> $tractoredShips 
     */
    private function triggerAlertReaction(
        SpacecraftWrapperInterface $wrapper,
        ?SpacecraftWrapperInterface $tractoredShipWrapper,
        array $tractoredShips,
        InformationInterface $info
    ): void {
        $spacecraft = $wrapper->get();
        $wasAliveBeforeAlert = !$spacecraft->getCondition()->isDestroyed();

        //Alarm-Rot check for ship
        $this->alertReactionFacade->doItAll($wrapper, $info);

        if ($tractoredShipWrapper !== null) {
            $tractoringShipForCheck = $spacecraft->getCondition()->isDestroyed() ? null : $spacecraft;
            $this->alertReactionFacade->doItAll($tractoredShipWrapper, $info, $tractoringShipForCheck);

            if (
                $wasAliveBeforeAlert
                && $spacecraft->getCondition()->isDestroyed()
                && !$tractoredShipWrapper->get()->getCondition()->isDestroyed()
            ) {
                $this->alertReactionFacade->doItAll($tractoredShipWrapper, $info, null);
            }
        } else if (!empty($tractoredShips)) {
            $tractoringShipsStateBeforeAlert = [];

            foreach ($tractoredShips as [$tractoringShip, $tractoredShipWrapper]) {
                $tractoringShipsStateBeforeAlert[$tractoringShip->getId()] = !$tractoringShip->getCondition()->isDestroyed();
            }

            foreach ($tractoredShips as [$tractoringShip, $tractoredShipWrapper]) {
                $tractoringShipForCheck = $tractoringShip->getCondition()->isDestroyed() ? null : $tractoringShip;
                $this->alertReactionFacade->doItAll($tractoredShipWrapper, $info, $tractoringShipForCheck);
            }

            foreach ($tractoredShips as [$tractoringShip, $tractoredShipWrapper]) {
                $wasAliveBeforeAlert = $tractoringShipsStateBeforeAlert[$tractoringShip->getId()] ?? false;

                if (
                    $wasAliveBeforeAlert
                    && $tractoringShip->getCondition()->isDestroyed()
                    && !$tractoredShipWrapper->get()->getCondition()->isDestroyed()
                ) {
                    $this->alertReactionFacade->doItAll($tractoredShipWrapper, $info, null);
                }
            }
        }
    }

    /** @return array<int, array{0: Ship, 1: SpacecraftWrapperInterface}> */
    private function getTractoredShipWrappers(FleetWrapperInterface $fleetWrapper): array
    {
        /** @var array<int, array{0: Ship, 1: SpacecraftWrapperInterface}> */
        $result = [];

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

            $tractoredWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredWrapper !== null) {
                $result[] = [$wrapper->get(), $tractoredWrapper];
            }
        }

        return $result;
    }

    #[\Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
