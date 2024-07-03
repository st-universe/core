<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateTractorBeam;

use Override;
use request;

use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;

final class DeactivateTractorBeam implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_DEACTIVATE_TRACTOR';

    public function __construct(private ShipLoaderInterface $shipLoader, private ActivatorDeactivatorHelperInterface $helper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if (!$ship->isTractoring()) {
            return;
        }
        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_TRACTOR_BEAM, $game);
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
