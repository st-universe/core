<?php

declare(strict_types=1);

namespace Stu\Module\Ship\Action\DeactivateRPGModule;

use request;
use Stu\Component\Ship\System\ShipSystemTypeEnum;
use Stu\Module\Control\ActionControllerInterface;
use Stu\Module\Control\GameControllerInterface;
use Stu\Module\Ship\Lib\ActivatorDeactivatorHelperInterface;
use Stu\Module\Ship\View\ShowShip\ShowShip;
use Stu\Module\Ship\Lib\ShipLoaderInterface;
use Stu\Orm\Repository\ShipRepositoryInterface;

final class DeactivateRPGModule implements ActionControllerInterface
{
    public const ACTION_IDENTIFIER = 'B_DEACTIVATE_RPG_Module';

    private ActivatorDeactivatorHelperInterface $helper;

    private ShipLoaderInterface $shipLoader;

    private ShipRepositoryInterface $shipRepository;

    public function __construct(
        ShipLoaderInterface $shipLoader,
        ShipRepositoryInterface $shipRepository,
        ActivatorDeactivatorHelperInterface $helper
    ) {
        $this->shipLoader = $shipLoader;
        $this->shipRepository = $shipRepository;
        $this->helper = $helper;
    }

    public function handle(GameControllerInterface $game): void
    {
        $game->setView(ShowShip::VIEW_IDENTIFIER);

        $userId = $game->getUser()->getId();

        $this->helper->deactivate(request::indInt('id'), ShipSystemTypeEnum::SYSTEM_RPG_MODULE, $game, true);

        $ship = $this->shipLoader->getByIdAndUser(
            request::indInt('id'),
            $userId
        );

        if ($ship->isDisabled() == true) {
            $ship->setDisabled(false);
        }

        $ship->setCanBeDisabled(false);

        $this->shipRepository->save($ship);

        $game->addInformation("Das RPG Modul wurde deaktiviert");
    }
    public function performSessionCheck(): bool
    {
        return true;
    }
}
