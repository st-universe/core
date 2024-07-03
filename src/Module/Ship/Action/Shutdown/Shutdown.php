<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\Shutdown;

use Override;
use request;
use Stu\Component\Ship\System\ShipSystemManagerInterface;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class Shutdown implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_SHUTDOWN';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper, private ShipLoaderInterface $shipLoader, private AlertReactionFacadeInterface $alertReactionFacade, private ShipSystemManagerInterface $shipSystemManager)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $ship = $wrapper->get();
        $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

        $triggerAlertRed = $ship->getWarpDriveState() || $ship->getCloakState();

        //deactivate all systems except life support and troop quarters
        foreach ($this->shipSystemManager->getActiveSystems($ship) as $system) {
            if (
                $system->getSystemType() !== ShipSystemTypeEnum::SYSTEM_LIFE_SUPPORT &&
                $system->getSystemType() !== ShipSystemTypeEnum::SYSTEM_TROOP_QUARTERS
            ) {
                $this->helper->deactivate(request::indInt('id'), $system->getSystemType(), $game);
            }
        }

        //set alert to green
        $ship->setAlertStateGreen();

        $game->addInformation(_("Der Energieverbrauch wurde auf ein Minimum reduziert"));

        if ($triggerAlertRed) {
            //Alarm-Rot check for ship
            $this->alertReactionFacade->doItAll($wrapper, $game);

            //Alarm-Rot check for traktor ship
            if ($traktoredShipWrapper !== null) {
                $this->alertReactionFacade->doItAll($traktoredShipWrapper, $game, $ship);
            }

            if ($ship->isDestroyed()) {
                return;
            }
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
