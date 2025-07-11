<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateWarp;

use Override;
use request;
use RuntimeException;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Component\Spacecraft\System\Control\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\Lib\ShipWrapperInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;
use Stu\Orm\Entity\Ship;

final class FleetDeactivateWarp implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_WARP';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper, private ShipLoaderInterface $shipLoader, private AlertReactionFacadeInterface $alertReactionFacade) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $success =  $this->helper->deactivateFleet(
            $wrapper,
            SpacecraftSystemTypeEnum::WARPDRIVE,
            $game
        );

        if ($success) {
            $ship = $wrapper->get();
            $tractoredShips = $this->getTractoredShipWrappers($wrapper);

            //Alarm-Rot check for fleet
            $this->alertReactionFacade->doItAll($wrapper, $game);

            //Alarm-Rot check for tractored ships
            foreach ($tractoredShips as [$tractoringShipWrapper, $tractoredShipWrapper]) {
                $this->alertReactionFacade->doItAll($tractoredShipWrapper, $game, $tractoringShipWrapper);
            }

            if ($ship->getCondition()->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    /** @return array<int, array{0: Ship, 1: ShipWrapperInterface}> */
    private function getTractoredShipWrappers(ShipWrapperInterface $leader): array
    {
        /** @var array<int, array{0: Ship, 1: ShipWrapperInterface}> */
        $result = [];

        $fleetWrapper = $leader->getFleetWrapper();
        if ($fleetWrapper === null) {
            throw new RuntimeException('this should not happen');
        }

        foreach ($fleetWrapper->getShipWrappers() as $wrapper) {

            $tractoredWrapper = $wrapper->getTractoredShipWrapper();
            if ($tractoredWrapper !== null) {
                $result[] = [$wrapper->get(), $tractoredWrapper];
            }
        }

        return $result;
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
