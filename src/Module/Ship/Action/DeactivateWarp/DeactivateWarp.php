<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateWarp;

use Override;
use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateWarp implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_WARP';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper, private ShipLoaderInterface $shipLoader, private AlertReactionFacadeInterface $alertReactionFacade)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $game->getUser()->getId()
        );

        $success = $this->helper->deactivate(
            $wrapper,
            ShipSystemTypeEnum::SYSTEM_WARPDRIVE,
            $game
        );

        if ($success) {
            $ship = $wrapper->get();
            $traktoredShipWrapper = $wrapper->getTractoredShipWrapper();

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
