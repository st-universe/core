<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\ActivateRPGModule;

use Override;
use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class ActivateRPGModule implements ActionControllerInterface
{
    public const string ACTION_IDENTIFIER = 'B_ACTIVATE_RPG_MODULE';

    public function __construct(private ShipLoaderInterface $shipLoader, private ShipRepositoryInterface $shipRepository, private ActivatorDeactivatorHelperInterface $helper)
    {
    }

    #[Override]
    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->helper->activate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_RPG_MODULE, $game, true);

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );


        $this->shipRepository->save($ship);

        $game->addInformation("Das RPG Modul wurde aktiviert");
    }

    #[Override]
    public function performSessionCheck(): bool
    {
        return true;
    }
}
