<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateCloak;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\Battle\AlertDetection\AlertReactionFacadeInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateCloak implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_CLOAK';

    private ActivatorDeactivatorHelperInterface $helper;

    private AlertReactionFacadeInterface $alertReactionFacade;

    private ShipLoaderInterface $shipLoader;


    public function __construct(
        ActivatorDeactivatorHelperInterface $helper,
        ShipLoaderInterface $shipLoader,
        AlertReactionFacadeInterface $alertReactionFacade
    ) {
        $this->helper = $helper;
        $this->alertReactionFacade = $alertReactionFacade;
        $this->shipLoader = $shipLoader;
    }

    public function handle(GameControllerInterface $game): void
    {
        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_CLOAK, $game);

        $userId = $game->getUser()->getId();

        $wrapper = $this->shipLoader->getWrapperByIdAndUser(
            request::indInt('id'),
            $userId
        );

        //Alarm-Rot check
        $this->alertReactionFacade->doItAll($wrapper, $game);

        if ($wrapper->get()->isDestroyed()) {
            return;
        }

        $game->setView(ShowShip::VIEW_IDENTIFIER);
    }

    public function performSessionCheck(): bool
    {
        return true;
    }
}
