<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateCloak;

use Override;
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
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_CLOAK';


    public function __construct(private ActivatorDeactivatorHelperInterface $helper, private ShipLoaderInterface $shipLoader, private AlertReactionFacadeInterface $alertReactionFacade)
    {
    }

    #[Override]
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

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
