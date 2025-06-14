<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\FleetDeactivateCloak;

use Override;
use request;
use Stu\Component\Spacecraft\System\SpacecraftSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Spacecraft\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Spacecraft\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Spacecraft\View\ShowSpacecraft\ShowSpacecraft;

final class FleetDeactivateCloak implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_FLEET_DEACTIVATE_CLOAK';

    public function __construct(private ActivatorDeactivatorHelperInterface $helper, private ShipLoaderInterface $shipLoader, private AlertReactionFacadeInterface $alertReactionFacade) {}

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $this->helper->deactivateFleet(request::indInt('id'), SpacecraftSystemTypeEnum::CLOAK, $game);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        //Alarm-Rot check
        $this->alertReactionFacade->doItAll($wrapper, $game);

        if ($wrapper->get()->getCondition()->isDestroyed()) {
            return;
        }

        $game->setView(ShowSpacecraft::VIEW_IDENTIFIER);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
